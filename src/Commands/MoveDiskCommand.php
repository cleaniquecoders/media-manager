<?php

namespace CleaniqueCoders\MediaManager\Commands;

use CleaniqueCoders\MediaManager\MediaManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;
use Throwable;

class MoveDiskCommand extends Command
{
    public $signature = 'media-manager:move-disk
        {--from= : Disk the media currently lives on}
        {--to= : Disk to move the media to (may equal --from for a re-layout)}
        {--from-path-generator= : PathGenerator class the files are currently laid out with (default: configured)}
        {--to-path-generator= : PathGenerator class to lay the files out with (default: configured)}
        {--collection= : Only move media of this collection}
        {--chunk=100 : Number of media rows per chunk}
        {--dry-run : Show what would move without changing anything}
        {--delete-source : Delete the source files once copied and verified}
        {--force : Skip the confirmation prompt}';

    public $description = 'Move Media Library files between disks and/or path layouts and update the media rows';

    protected const DRY_RUN_LINES = 20;

    protected array $summary = ['moved' => 0, 'skipped' => 0, 'failed' => 0];

    /** @var array<int, string> */
    protected array $failures = [];

    protected int $dryRunLines = 0;

    public function handle(): int
    {
        // The command instance is reused when called repeatedly in one process (e.g. once per tenant)
        $this->summary = ['moved' => 0, 'skipped' => 0, 'failed' => 0];
        $this->failures = [];
        $this->dryRunLines = 0;

        $from = (string) $this->option('from');
        $to = (string) $this->option('to');

        if ($from === '' || $to === '') {
            $this->error('Both --from and --to are required.');

            return self::FAILURE;
        }

        try {
            $fromGenerator = $this->generatorOption('from-path-generator');
            $toGenerator = $this->generatorOption('to-path-generator');
            Storage::disk($from);
            Storage::disk($to);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        if ($from === $to && $this->generatorClass($fromGenerator) === $this->generatorClass($toGenerator)) {
            $this->error('Nothing to do: --from equals --to and both path generators resolve to the same class.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if (! $dryRun && ! $this->option('force')
            && ! $this->confirm("Move media files from [{$from}] to [{$to}]?")) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $mediaModel = MediaManager::mediaModel();
        $query = $mediaModel::query()
            ->where(fn ($q) => $q->where('disk', $from)->orWhere('conversions_disk', $from))
            ->when($this->option('collection'), fn ($q, $collection) => $q->where('collection_name', $collection));

        $chunkNumber = 0;

        $query->chunkById(max(1, (int) $this->option('chunk')), function ($chunk) use ($from, $to, $fromGenerator, $toGenerator, $dryRun, &$chunkNumber) {
            $chunkNumber++;

            foreach ($chunk as $media) {
                $this->processMedia($media, $from, $to, $fromGenerator, $toGenerator, $dryRun);
            }

            if (! $dryRun) {
                $this->line(sprintf(
                    'Chunk %d: %d media (moved %d, skipped %d, failed %d so far)',
                    $chunkNumber, $chunk->count(), $this->summary['moved'], $this->summary['skipped'], $this->summary['failed']
                ));
            }
        });

        if ($dryRun) {
            if ($this->dryRunLines > self::DRY_RUN_LINES) {
                $this->line('... and '.($this->dryRunLines - self::DRY_RUN_LINES).' more.');
            }
            $this->info("Dry run: {$this->dryRunLines} media would be moved. Nothing was changed.");

            return self::SUCCESS;
        }

        $this->table(['Moved', 'Skipped', 'Failed'], [array_values($this->summary)]);

        foreach ($this->failures as $failure) {
            $this->error($failure);
        }

        return $this->summary['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function processMedia(Media $media, string $from, string $to, ?PathGenerator $fromGenerator, ?PathGenerator $toGenerator, bool $dryRun): void
    {
        try {
            $source = $fromGenerator ?? PathGeneratorFactory::create($media);
            $target = $toGenerator ?? PathGeneratorFactory::create($media);
            $files = $this->ownedFiles($media, $from, $to, $source, $target);

            if ($files === []) {
                $this->summary['skipped']++;

                return;
            }

            if ($dryRun) {
                $this->dryRunLines++;
                if ($this->dryRunLines <= self::DRY_RUN_LINES) {
                    foreach ($files as $file) {
                        $this->line("#{$media->getKey()} {$file['type']}: {$from}:{$file['from']} -> {$to}:{$file['to']}");
                    }
                }

                return;
            }

            $sourceDisk = Storage::disk($from);
            $targetDisk = Storage::disk($to);
            $copied = 0;
            $present = [];

            foreach ($files as $file) {
                if (! $sourceDisk->exists($file['from'])) {
                    // Already moved by an earlier run, or a conversion that was never written
                    if ($file['type'] === 'original' && ! $targetDisk->exists($file['to'])) {
                        throw new RuntimeException("Original file not found at [{$from}:{$file['from']}] nor [{$to}:{$file['to']}].");
                    }

                    continue;
                }

                $copied += $this->copyFile($from, $file['from'], $to, $file['to']) ? 1 : 0;
                $present[] = $file;
            }

            $this->updateRow($media, $from, $to);

            $this->summary[$copied > 0 ? 'moved' : 'skipped']++;
        } catch (Throwable $e) {
            $this->summary['failed']++;
            $this->failures[] = "#{$media->getKey()} {$media->file_name}: {$e->getMessage()}";

            return;
        }

        // The row already points at the destination, so a cleanup error is a warning, not a failure
        if ($this->option('delete-source')) {
            try {
                $this->deleteSource($media, $from, $present);
            } catch (Throwable $e) {
                $this->warn("#{$media->getKey()}: source cleanup incomplete: {$e->getMessage()}");
            }
        }
    }

    /**
     * The exact files this media owns on --from, by name. Directories are never listed:
     * a directory such as DefaultPathGenerator's {id}/ can be shared with media of another
     * database that happens to have the same id.
     *
     * @return array<int, array{type: string, from: string, to: string, base: string}>
     */
    protected function ownedFiles(Media $media, string $from, string $to, PathGenerator $source, PathGenerator $target): array
    {
        $files = [];
        $add = function (string $type, string $sourceDir, string $targetDir, string $fileName) use (&$files) {
            $files[] = [
                'type' => $type,
                'from' => $this->join($sourceDir, $fileName),
                'to' => $this->join($targetDir, $fileName),
                'base' => $this->dir($sourceDir),
            ];
        };

        if ($media->disk === $from) {
            $add('original', $source->getPath($media), $target->getPath($media), $media->file_name);
        }

        if (($media->conversions_disk ?: $media->disk) === $from) {
            foreach ($media->getGeneratedConversions()->filter(fn ($generated) => $generated === true)->keys() as $conversion) {
                // Only the file name: it does not depend on the path generator
                $fileName = basename($media->getPathRelativeToRoot((string) $conversion));
                $add('conversion', $source->getPathForConversions($media), $target->getPathForConversions($media), $fileName);
            }

            // Each set's base64svg placeholder is stored inline in the row, not as a file
            foreach ((array) $media->responsive_images as $set) {
                foreach ($set['urls'] ?? [] as $fileName) {
                    $add('responsive', $source->getPathForResponsiveImages($media), $target->getPathForResponsiveImages($media), basename($fileName));
                }
            }
        }

        return array_values(array_filter($files, fn ($file) => $from !== $to || $file['from'] !== $file['to']));
    }

    /**
     * Copy one file by streaming, then verify it. Returns false when the destination
     * already held an identical-size copy.
     */
    protected function copyFile(string $from, string $sourcePath, string $to, string $targetPath): bool
    {
        $source = Storage::disk($from);
        $target = Storage::disk($to);
        $size = $source->size($sourcePath);

        if ($target->exists($targetPath) && $target->size($targetPath) === $size) {
            return false;
        }

        $stream = $source->readStream($sourcePath);

        if (! is_resource($stream)) {
            throw new RuntimeException("Unable to read [{$from}:{$sourcePath}].");
        }

        try {
            if ($target->writeStream($targetPath, $stream) === false) {
                throw new RuntimeException("Unable to write [{$to}:{$targetPath}].");
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $target->exists($targetPath) || $target->size($targetPath) !== $size) {
            throw new RuntimeException("Verification failed for [{$to}:{$targetPath}].");
        }

        return true;
    }

    protected function updateRow(Media $media, string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        if (($media->conversions_disk ?: $media->disk) === $from) {
            $media->conversions_disk = $to;
        }

        if ($media->disk === $from) {
            $media->disk = $to;
        }

        $media->timestamps = false;
        $media->saveQuietly();
        $media->timestamps = true;
    }

    /**
     * Delete exactly this media's files, then any directory of theirs left completely empty.
     *
     * @param  array<int, array{type: string, from: string, to: string, base: string}>  $files
     */
    protected function deleteSource(Media $media, string $from, array $files): void
    {
        $disk = Storage::disk($from);
        $directories = [];

        foreach ($files as $file) {
            if (! $disk->delete($file['from'])) {
                $this->warn("#{$media->getKey()}: could not delete [{$from}:{$file['from']}].");
            }

            // Climb from the file's directory up to the generator's directory, never above it
            for ($dir = dirname($file['from']); $dir !== '.' && $dir !== '' && str_starts_with($dir.'/', $file['base'].'/'); $dir = dirname($dir)) {
                $directories[] = $dir;
            }
        }

        $directories = array_unique($directories);
        usort($directories, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($directories as $dir) {
            if ($disk->directoryExists($dir) && $this->isEmpty($disk, $dir)) {
                $disk->deleteDirectory($dir);
            }
        }
    }

    protected function isEmpty(Filesystem $disk, string $dir): bool
    {
        return $disk->allFiles($dir) === [] && $disk->allDirectories($dir) === [];
    }

    protected function dir(string $path): string
    {
        return trim($path, '/');
    }

    protected function join(string $directory, string $fileName): string
    {
        return ltrim($this->dir($directory).'/'.$fileName, '/');
    }

    protected function generatorOption(string $option): ?PathGenerator
    {
        $class = ltrim((string) $this->option($option), '\\');

        if ($class === '') {
            return null;
        }

        if (! class_exists($class) || ! is_subclass_of($class, PathGenerator::class)) {
            throw new RuntimeException("--{$option} [{$class}] is not a class implementing ".PathGenerator::class.'.');
        }

        return app($class);
    }

    protected function generatorClass(?PathGenerator $generator): string
    {
        return $generator !== null ? $generator::class : ltrim((string) config('media-library.path_generator'), '\\');
    }
}
