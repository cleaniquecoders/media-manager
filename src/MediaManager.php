<?php

namespace CleaniqueCoders\MediaManager;

use CleaniqueCoders\MediaManager\Services\MediaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaManager
{
    protected MediaService $service;

    public function __construct(MediaService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all media with optional filtering and pagination.
     */
    public function browse(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        return $this->service->getMedia(
            $filters,
            $perPage ?? config('media-manager.browser.items_per_page', 24)
        );
    }

    /**
     * Upload a file to a model's collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function upload(Model $model, UploadedFile $file, string $collection = 'default', array $properties = []): Media
    {
        return $this->service->upload($model, $file, $collection, $properties);
    }

    /**
     * Upload multiple files to a model's collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function uploadMultiple(Model $model, array $files, string $collection = 'default', array $properties = []): Collection
    {
        return $this->service->uploadMultiple($model, $files, $collection, $properties);
    }

    /**
     * Get media for a specific model.
     *
     * @param  Model&HasMedia  $model
     */
    public function forModel(Model $model, ?string $collection = null): Collection
    {
        return $this->service->getMediaForModel($model, $collection);
    }

    /**
     * Find a media item by ID.
     */
    public function find(int $id): ?Media
    {
        return $this->service->find($id);
    }

    /**
     * Find multiple media items by IDs.
     */
    public function findMany(array $ids): Collection
    {
        return $this->service->findMany($ids);
    }

    /**
     * Update media properties.
     */
    public function updateProperties(Media $media, array $properties): Media
    {
        return $this->service->updateProperties($media, $properties);
    }

    /**
     * Rename a media item.
     */
    public function rename(Media $media, string $name): Media
    {
        return $this->service->rename($media, $name);
    }

    /**
     * Replace a media file while keeping metadata.
     */
    public function replace(Media $media, UploadedFile $file): Media
    {
        return $this->service->replace($media, $file);
    }

    /**
     * Delete a media item.
     */
    public function delete(Media $media): bool
    {
        return $this->service->delete($media);
    }

    /**
     * Delete multiple media items.
     */
    public function deleteMultiple(array $mediaIds): int
    {
        return $this->service->deleteMultiple($mediaIds);
    }

    /**
     * Reorder media items within a collection.
     *
     * @param  Model&HasMedia  $model
     */
    public function reorder(Model $model, string $collection, array $order): void
    {
        $this->service->reorder($model, $collection, $order);
    }

    /**
     * Get all unique collections.
     */
    public function collections(): Collection
    {
        return $this->service->getCollections();
    }

    /**
     * Get all unique MIME types.
     */
    public function mimeTypes(): Collection
    {
        return $this->service->getMimeTypes();
    }

    /**
     * Validate a file against configuration.
     */
    public function validate(UploadedFile $file): array
    {
        return $this->service->validateFile($file);
    }

    /**
     * Format file size for display.
     */
    public function formatSize(int $bytes): string
    {
        return $this->service->formatFileSize($bytes);
    }

    /**
     * Get file type category from MIME type.
     */
    public function getTypeCategory(string $mimeType): string
    {
        return $this->service->getFileTypeCategory($mimeType);
    }

    /**
     * Clean up expired temporary uploads.
     */
    public function cleanupTemporary(): int
    {
        return $this->service->cleanupTemporary();
    }

    /**
     * Get the underlying service instance.
     */
    public function service(): MediaService
    {
        return $this->service;
    }
}
