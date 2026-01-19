<?php

namespace CleaniqueCoders\MediaManager\Services;

use CleaniqueCoders\MediaManager\Support\MediaFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function __construct(
        protected MediaFilter $filter
    ) {}

    /**
     * Get all media with optional filtering and pagination.
     */
    public function getMedia(array $filters = [], int $perPage = 24): LengthAwarePaginator
    {
        $query = Media::query()->latest();

        return $this->filter->apply($query, $filters)->paginate($perPage);
    }

    /**
     * Get media for a specific model and collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function getMediaForModel(Model $model, ?string $collection = null): Collection
    {
        if ($collection) {
            return $model->getMedia($collection);
        }

        return $model->media;
    }

    /**
     * Get all unique collections.
     */
    public function getCollections(): Collection
    {
        return Media::query()
            ->select('collection_name')
            ->distinct()
            ->pluck('collection_name');
    }

    /**
     * Get all unique MIME types.
     */
    public function getMimeTypes(): Collection
    {
        return Media::query()
            ->select('mime_type')
            ->distinct()
            ->pluck('mime_type');
    }

    /**
     * Upload a file to a model's collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function upload(Model $model, UploadedFile $file, string $collection = 'default', array $customProperties = []): Media
    {
        $media = $model->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);

        return $media;
    }

    /**
     * Upload multiple files to a model's collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function uploadMultiple(Model $model, array $files, string $collection = 'default', array $customProperties = []): Collection
    {
        $uploaded = collect();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploaded->push($this->upload($model, $file, $collection, $customProperties));
            }
        }

        return $uploaded;
    }

    /**
     * Store a temporary upload before attaching to a model.
     */
    public function storeTemporary(UploadedFile $file): array
    {
        $disk = config('media-manager.temporary_disk', 'local');
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = "{$uuid}.{$extension}";

        $path = $file->storeAs('media-manager-tmp', $filename, $disk);

        return [
            'uuid' => $uuid,
            'path' => $path,
            'disk' => $disk,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Attach a temporary upload to a model.
     *
     * @param  Model&HasMedia  $model
     */
    public function attachTemporary(Model $model, array $temporaryFile, string $collection = 'default', array $customProperties = []): Media
    {
        $disk = $temporaryFile['disk'];
        $path = Storage::disk($disk)->path($temporaryFile['path']);

        $media = $model->addMedia($path)
            ->usingName(pathinfo($temporaryFile['original_name'], PATHINFO_FILENAME))
            ->usingFileName($temporaryFile['original_name'])
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);

        return $media;
    }

    /**
     * Update media custom properties.
     */
    public function updateProperties(Media $media, array $properties): Media
    {
        foreach ($properties as $key => $value) {
            $media->setCustomProperty($key, $value);
        }
        $media->save();

        return $media;
    }

    /**
     * Update media name.
     */
    public function rename(Media $media, string $name): Media
    {
        $media->name = $name;
        $media->save();

        return $media;
    }

    /**
     * Replace media file while keeping metadata.
     */
    public function replace(Media $media, UploadedFile $file): Media
    {
        /** @var Model&HasMedia $model */
        $model = $media->model;
        $collection = $media->collection_name;
        $customProperties = $media->custom_properties;
        $order = $media->order_column;

        // Delete old media
        $media->delete();

        // Upload new file with same properties
        $newMedia = $model->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);

        // Restore order
        $newMedia->order_column = $order;
        $newMedia->save();

        return $newMedia;
    }

    /**
     * Delete a media item.
     */
    public function delete(Media $media): bool
    {
        return $media->delete();
    }

    /**
     * Delete multiple media items.
     */
    public function deleteMultiple(array $mediaIds): int
    {
        return Media::whereIn('id', $mediaIds)->get()->each->delete()->count();
    }

    /**
     * Reorder media items within a collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function reorder(Model $model, string $collection, array $order): void
    {
        Media::setNewOrder($order);
    }

    /**
     * Get a single media by ID.
     */
    public function find(int $id): ?Media
    {
        return Media::find($id);
    }

    /**
     * Get multiple media by IDs.
     */
    public function findMany(array $ids): Collection
    {
        return Media::whereIn('id', $ids)->get();
    }

    /**
     * Validate file against configuration.
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];
        $maxSize = config('media-manager.upload.max_file_size', 10 * 1024 * 1024);
        $allowedMimes = config('media-manager.upload.allowed_mimes', []);

        if ($file->getSize() > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size of '.number_format($maxSize / 1024 / 1024, 2).' MB.';
        }

        if (! empty($allowedMimes) && ! in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File type '.$file->getMimeType().' is not allowed.';
        }

        return $errors;
    }

    /**
     * Clean up expired temporary uploads.
     */
    public function cleanupTemporary(): int
    {
        $disk = config('media-manager.temporary_disk', 'local');
        $expiration = config('media-manager.temporary_upload_expiration', 24);
        $files = Storage::disk($disk)->files('media-manager-tmp');
        $deleted = 0;

        foreach ($files as $file) {
            $lastModified = Storage::disk($disk)->lastModified($file);
            if ($lastModified < now()->subHours($expiration)->timestamp) {
                Storage::disk($disk)->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Get file type category from MIME type.
     */
    public function getFileTypeCategory(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            $mimeType === 'application/pdf' => 'pdf',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'document',
            str_contains($mimeType, 'sheet') || str_contains($mimeType, 'excel') => 'spreadsheet',
            default => 'file',
        };
    }

    /**
     * Format file size for display.
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
