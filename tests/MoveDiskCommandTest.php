<?php

use CleaniqueCoders\MediaManager\Tests\Fixtures\FaultyFilesystemAdapter;
use CleaniqueCoders\MediaManager\Tests\Fixtures\TenantPathGenerator;
use CleaniqueCoders\MediaManager\Tests\Fixtures\TestPostWithConversions;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

function faultyDisk(string $name): FaultyFilesystemAdapter
{
    $root = storage_path("framework/testing/disks/{$name}");
    (new Filesystem)->deleteDirectory($root);

    $adapter = new LocalFilesystemAdapter($root);
    $disk = new FaultyFilesystemAdapter(new Flysystem($adapter), $adapter, ['root' => $root]);

    config()->set("filesystems.disks.{$name}", ['driver' => 'local', 'root' => $root]);
    Storage::set($name, $disk);

    return $disk;
}

/**
 * Add a media with a conversion and a responsive image placed by hand,
 * laid out the way DefaultPathGenerator does it.
 */
function mediaWithConversions(string $collection = 'gallery', ?string $conversionsDisk = null): Media
{
    $post = TestPostWithConversions::create(['title' => 'Post']);

    $adder = $post->addMedia(UploadedFile::fake()->image('photo.jpg', 20, 20));

    if ($conversionsDisk !== null) {
        $adder->storingConversionsOnDisk($conversionsDisk);
    }

    $media = $adder->toMediaCollection($collection, 'media');
    $media->markAsConversionGenerated('thumb');
    $media->responsive_images = [
        'media_library_original' => ['urls' => ['photo___media_library_original_10_10.jpg'], 'base64svg' => 'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4='],
    ];
    $media->saveQuietly();
    $id = $media->getKey();
    $disk = Storage::disk($media->conversions_disk);

    $disk->put("{$id}/conversions/photo-thumb.jpg", 'thumb-bytes');
    $disk->put("{$id}/responsive-images/photo___media_library_original_10_10.jpg", 'responsive-bytes');

    return $media;
}

beforeEach(function () {
    foreach (['media', 'thumbs'] as $name) {
        Storage::fake($name);
        config()->set("filesystems.disks.{$name}", ['driver' => 'local', 'root' => storage_path("framework/testing/disks/{$name}")]);
    }

    $this->s3 = faultyDisk('s3');
});

it('moves original, conversions and responsive images to another disk and updates the row', function () {
    $media = mediaWithConversions();
    $id = $media->getKey();
    $updatedAt = $media->updated_at->toDateTimeString();
    $this->travel(1)->hour();

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--force' => true])
        ->expectsTable(['Moved', 'Skipped', 'Failed'], [[1, 0, 0]])
        ->assertExitCode(0);

    expect(Storage::disk('s3')->allFiles((string) $id))->toEqualCanonicalizing([
        "{$id}/photo.jpg",
        "{$id}/conversions/photo-thumb.jpg",
        "{$id}/responsive-images/photo___media_library_original_10_10.jpg",
    ]);
    expect(Storage::disk('s3')->get("{$id}/conversions/photo-thumb.jpg"))->toBe('thumb-bytes');

    $media->refresh();
    expect($media->disk)->toBe('s3')
        ->and($media->conversions_disk)->toBe('s3')
        ->and($media->updated_at->toDateTimeString())->toBe($updatedAt);

    // Source is kept without --delete-source
    Storage::disk('media')->assertExists("{$id}/photo.jpg");
});

it('re-lays out files on the same disk and sweeps after the generator is switched', function () {
    $media = mediaWithConversions();
    $id = $media->getKey();

    $this->artisan('media-manager:move-disk', [
        '--from' => 'media',
        '--to' => 'media',
        '--to-path-generator' => '\\'.TenantPathGenerator::class,
        '--delete-source' => true,
        '--force' => true,
    ])->assertExitCode(0);

    expect(Storage::disk('media')->allFiles('tenant-a'))->toEqualCanonicalizing([
        "tenant-a/originals/{$id}/photo.jpg",
        "tenant-a/conversions/{$id}/photo-thumb.jpg",
        "tenant-a/responsive/{$id}/photo___media_library_original_10_10.jpg",
    ]);
    expect(Storage::disk('media')->directoryExists((string) $id))->toBeFalse();
    expect($media->refresh()->disk)->toBe('media');

    // Sweep after the app flipped its generator: nothing left to move
    config()->set('media-library.path_generator', TenantPathGenerator::class);

    $this->artisan('media-manager:move-disk', [
        '--from' => 'media',
        '--to' => 'media',
        '--from-path-generator' => 'Spatie\\MediaLibrary\\Support\\PathGenerator\\DefaultPathGenerator',
        '--force' => true,
    ])->expectsTable(['Moved', 'Skipped', 'Failed'], [[0, 1, 0]])
        ->assertExitCode(0);
});

it('moves only the part stored on --from when conversions live on another disk', function () {
    $media = mediaWithConversions(conversionsDisk: 'thumbs');
    $id = $media->getKey();

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--force' => true])
        ->assertExitCode(0);

    expect(Storage::disk('s3')->allFiles((string) $id))->toBe(["{$id}/photo.jpg"]);
    $media->refresh();
    expect($media->disk)->toBe('s3')->and($media->conversions_disk)->toBe('thumbs');

    $this->artisan('media-manager:move-disk', ['--from' => 'thumbs', '--to' => 's3', '--force' => true])
        ->assertExitCode(0);

    expect(Storage::disk('s3')->allFiles((string) $id))->toHaveCount(3);
    $media->refresh();
    expect($media->disk)->toBe('s3')->and($media->conversions_disk)->toBe('s3');
});

it('changes nothing on a dry run', function () {
    $media = mediaWithConversions();

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--dry-run' => true])
        ->expectsOutputToContain("#{$media->getKey()} original: media:{$media->getKey()}/photo.jpg -> s3:{$media->getKey()}/photo.jpg")
        ->expectsOutputToContain("#{$media->getKey()} conversion: media:{$media->getKey()}/conversions/photo-thumb.jpg -> s3:{$media->getKey()}/conversions/photo-thumb.jpg")
        ->expectsOutputToContain('1 media would be moved')
        ->assertExitCode(0);

    expect(Storage::disk('s3')->allFiles())->toBe([])
        ->and($media->refresh()->disk)->toBe('media');
});

it('deletes only the copied source files with --delete-source', function () {
    $moved = mediaWithConversions('gallery');
    $other = mediaWithConversions('documents');
    Storage::disk('media')->put('keep.txt', 'unrelated');

    $this->artisan('media-manager:move-disk', [
        '--from' => 'media', '--to' => 's3', '--collection' => 'gallery', '--delete-source' => true, '--force' => true,
    ])->assertExitCode(0);

    expect(Storage::disk('media')->directoryExists((string) $moved->getKey()))->toBeFalse();
    expect(Storage::disk('media')->allFiles((string) $other->getKey()))->toHaveCount(3);
    Storage::disk('media')->assertExists('keep.txt');
    expect($other->refresh()->disk)->toBe('media');
});

it('leaves the row and source intact when a copy fails and reports failure', function () {
    $broken = mediaWithConversions();
    $fine = mediaWithConversions();
    $this->s3->failOn = ["{$broken->getKey()}/conversions/"];

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--delete-source' => true, '--force' => true])
        ->expectsTable(['Moved', 'Skipped', 'Failed'], [[1, 0, 1]])
        ->expectsOutputToContain('Simulated write failure')
        ->assertExitCode(1);

    expect($broken->refresh()->disk)->toBe('media')
        ->and(Storage::disk('media')->allFiles((string) $broken->getKey()))->toHaveCount(3)
        ->and($fine->refresh()->disk)->toBe('s3');
});

it('is idempotent and resumes an interrupted run', function () {
    $media = mediaWithConversions();
    $id = $media->getKey();

    // Interrupted after copying, before the row was updated
    foreach (Storage::disk('media')->allFiles((string) $id) as $path) {
        Storage::disk('s3')->put($path, Storage::disk('media')->get($path));
    }

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--delete-source' => true, '--force' => true])
        ->expectsTable(['Moved', 'Skipped', 'Failed'], [[0, 1, 0]])
        ->assertExitCode(0);

    expect($media->refresh()->disk)->toBe('s3')
        ->and(Storage::disk('media')->allFiles())->toBe([]);

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--force' => true])
        ->expectsTable(['Moved', 'Skipped', 'Failed'], [[0, 0, 0]])
        ->assertExitCode(0);

    expect(Storage::disk('s3')->allFiles((string) $id))->toHaveCount(3);
});

it('refuses when there is nothing to do', function () {
    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 'media', '--force' => true])
        ->expectsOutputToContain('Nothing to do')
        ->assertExitCode(1);
});

it('asks for confirmation unless forced', function () {
    $media = mediaWithConversions();

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3'])
        ->expectsConfirmation('Move media files from [media] to [s3]?', 'no')
        ->assertExitCode(1);

    expect($media->refresh()->disk)->toBe('media');
});

it('copies and deletes only the files the media owns, never a foreign file sharing its directory', function () {
    $media = mediaWithConversions();
    $id = $media->getKey();
    $disk = Storage::disk('media');

    // Another database's media with the same id, sharing DefaultPathGenerator's {id}/ directory
    $disk->put("{$id}/other-tenant.jpg", 'foreign');
    $disk->put("{$id}/conversions/other-tenant-thumb.jpg", 'foreign-thumb');

    $this->artisan('media-manager:move-disk', [
        '--from' => 'media',
        '--to' => 'media',
        '--to-path-generator' => TenantPathGenerator::class,
        '--delete-source' => true,
        '--force' => true,
    ])->assertExitCode(0);

    expect($disk->allFiles('tenant-a'))->toEqualCanonicalizing([
        "tenant-a/originals/{$id}/photo.jpg",
        "tenant-a/conversions/{$id}/photo-thumb.jpg",
        "tenant-a/responsive/{$id}/photo___media_library_original_10_10.jpg",
    ]);
    expect($disk->allFiles((string) $id))->toEqualCanonicalizing([
        "{$id}/other-tenant.jpg",
        "{$id}/conversions/other-tenant-thumb.jpg",
    ]);
    expect($disk->get("{$id}/other-tenant.jpg"))->toBe('foreign');
});

it('skips a missing conversion file but fails a media whose original is missing', function () {
    $missingThumb = mediaWithConversions();
    Storage::disk('media')->delete("{$missingThumb->getKey()}/conversions/photo-thumb.jpg");

    $missingOriginal = mediaWithConversions();
    Storage::disk('media')->delete("{$missingOriginal->getKey()}/photo.jpg");

    $this->artisan('media-manager:move-disk', ['--from' => 'media', '--to' => 's3', '--force' => true])
        ->expectsTable(['Moved', 'Skipped', 'Failed'], [[1, 0, 1]])
        ->expectsOutputToContain('Original file not found')
        ->assertExitCode(1);

    expect($missingThumb->refresh()->disk)->toBe('s3')
        ->and($missingOriginal->refresh()->disk)->toBe('media')
        ->and(Storage::disk('media')->allFiles((string) $missingOriginal->getKey()))->toHaveCount(2);
});
