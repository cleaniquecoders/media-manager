<?php

namespace CleaniqueCoders\MediaManager\Livewire\Concerns;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HandlesPreview
{
    /**
     * Get preview data for a media item.
     */
    protected function getPreviewData(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'size_formatted' => $this->formatBytes($media->size),
            'url' => $media->getUrl(),
            'thumbnail_url' => $this->getThumbnailUrl($media),
            'type' => $this->getMediaType($media),
            'collection' => $media->collection_name,
            'custom_properties' => $media->custom_properties,
            'created_at' => $media->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $media->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get thumbnail URL for a media item.
     */
    protected function getThumbnailUrl(Media $media): ?string
    {
        if ($this->isMediaImage($media)) {
            // Check if a thumbnail conversion exists
            if ($media->hasGeneratedConversion('thumb')) {
                return $media->getUrl('thumb');
            }

            return $media->getUrl();
        }

        return null;
    }

    /**
     * Get the type category of a media item.
     */
    protected function getMediaType(Media $media): string
    {
        $mimeType = $media->mime_type;

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
     * Check if media is an image.
     */
    protected function isMediaImage(Media $media): bool
    {
        return str_starts_with($media->mime_type, 'image/');
    }

    /**
     * Check if media is a video.
     */
    protected function isMediaVideo(Media $media): bool
    {
        return str_starts_with($media->mime_type, 'video/');
    }

    /**
     * Check if media is audio.
     */
    protected function isMediaAudio(Media $media): bool
    {
        return str_starts_with($media->mime_type, 'audio/');
    }

    /**
     * Check if media is a PDF.
     */
    protected function isMediaPdf(Media $media): bool
    {
        return $media->mime_type === 'application/pdf';
    }

    /**
     * Check if media can be previewed in browser.
     */
    protected function canPreview(Media $media): bool
    {
        return $this->isMediaImage($media)
            || $this->isMediaVideo($media)
            || $this->isMediaAudio($media)
            || $this->isMediaPdf($media);
    }

    /**
     * Get icon name for media type.
     */
    protected function getMediaIcon(Media $media): string
    {
        $type = $this->getMediaType($media);

        return match ($type) {
            'image' => 'photo',
            'video' => 'video',
            'audio' => 'music',
            'pdf' => 'document-text',
            'document' => 'document',
            'spreadsheet' => 'table',
            default => 'document',
        };
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
