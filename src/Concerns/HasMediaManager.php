<?php

namespace CleaniqueCoders\MediaManager\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasMediaManager
{
    use InteractsWithMedia;

    /**
     * Upload a single file to a collection.
     */
    public function uploadMedia(UploadedFile $file, string $collection = 'default', array $customProperties = []): Media
    {
        return $this->addMedia($file)
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);
    }

    /**
     * Upload multiple files to a collection.
     */
    public function uploadMediaMultiple(array $files, string $collection = 'default', array $customProperties = []): Collection
    {
        $uploaded = collect();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploaded->push($this->uploadMedia($file, $collection, $customProperties));
            }
        }

        return $uploaded;
    }

    /**
     * Get media from a collection with optional sorting.
     */
    public function getMediaOrdered(string $collection = 'default'): Collection
    {
        return $this->getMedia($collection)->sortBy('order_column')->values();
    }

    /**
     * Get the first media from a collection.
     */
    public function getFirstMediaOrNull(string $collection = 'default'): ?Media
    {
        return $this->getFirstMedia($collection);
    }

    /**
     * Check if the model has any media in a collection.
     */
    public function hasMediaInCollection(string $collection = 'default'): bool
    {
        return $this->getMedia($collection)->isNotEmpty();
    }

    /**
     * Get media count for a collection.
     */
    public function getMediaCount(string $collection = 'default'): int
    {
        return $this->getMedia($collection)->count();
    }

    /**
     * Clear all media from a specific collection.
     */
    public function clearMediaInCollection(string $collection): void
    {
        $this->clearMediaCollection($collection);
    }

    /**
     * Update the order of media items in a collection.
     */
    public function reorderMedia(string $collection, array $order): void
    {
        Media::setNewOrder($order);
    }

    /**
     * Replace all media in a collection with new files.
     */
    public function replaceMediaInCollection(array $files, string $collection = 'default', array $customProperties = []): Collection
    {
        $this->clearMediaCollection($collection);

        return $this->uploadMediaMultiple($files, $collection, $customProperties);
    }

    /**
     * Get all collections with media for this model.
     */
    public function getCollectionsWithMedia(): Collection
    {
        return $this->media->pluck('collection_name')->unique()->values();
    }

    /**
     * Get media grouped by collection.
     */
    public function getMediaGroupedByCollection(): Collection
    {
        return $this->media->groupBy('collection_name');
    }

    /**
     * Attach existing media to this model.
     */
    public function attachExistingMedia(int $mediaId, string $collection = 'default'): ?Media
    {
        $media = Media::find($mediaId);

        if (! $media) {
            return null;
        }

        // Copy the media to this model
        return $this->copyMedia($media->getPath())
            ->toMediaCollection($collection);
    }

    /**
     * Get the media relationship for querying.
     */
    public function mediaRelation(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    /**
     * Sync media for a collection (keeps only specified media IDs).
     */
    public function syncMedia(array $mediaIds, string $collection): void
    {
        $currentMedia = $this->getMedia($collection);
        $toDelete = $currentMedia->whereNotIn('id', $mediaIds);

        foreach ($toDelete as $media) {
            $media->delete();
        }
    }

    /**
     * Get the URL for the first media in a collection.
     */
    public function getMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Get all URLs for media in a collection.
     */
    public function getMediaUrls(string $collection = 'default', string $conversion = ''): Collection
    {
        return $this->getMedia($collection)->map(function (Media $media) use ($conversion) {
            return $conversion ? $media->getUrl($conversion) : $media->getUrl();
        });
    }
}
