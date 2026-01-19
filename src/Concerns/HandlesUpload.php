<?php

namespace CleaniqueCoders\MediaManager\Concerns;

use CleaniqueCoders\MediaManager\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesUpload
{
    /**
     * Validate an uploaded file against configuration.
     */
    protected function validateUploadedFile(TemporaryUploadedFile|UploadedFile $file): array
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
     * Get the accepted file types string for the input.
     */
    protected function getAcceptedTypesString(?array $acceptedTypes = null): string
    {
        $types = $acceptedTypes ?? config('media-manager.upload.allowed_mimes', []);

        if (empty($types)) {
            return '*';
        }

        return implode(',', $types);
    }

    /**
     * Get max file size in MB for display.
     */
    protected function getMaxFileSizeInMb(): float
    {
        $maxSize = config('media-manager.upload.max_file_size', 10 * 1024 * 1024);

        return round($maxSize / 1024 / 1024, 2);
    }

    /**
     * Format file size for display.
     */
    protected function formatFileSize(int $bytes): string
    {
        return app(MediaService::class)->formatFileSize($bytes);
    }

    /**
     * Determine file type category from MIME type.
     */
    protected function getFileTypeFromMime(string $mimeType): string
    {
        return app(MediaService::class)->getFileTypeCategory($mimeType);
    }

    /**
     * Check if file is an image.
     */
    protected function isImage(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Check if file is a video.
     */
    protected function isVideo(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'video/');
    }

    /**
     * Check if file is a PDF.
     */
    protected function isPdf(string $mimeType): bool
    {
        return $mimeType === 'application/pdf';
    }

    /**
     * Get a preview URL for an uploaded file.
     */
    protected function getPreviewUrl(TemporaryUploadedFile|UploadedFile $file): ?string
    {
        if ($file instanceof TemporaryUploadedFile && $this->isImage($file->getMimeType())) {
            return $file->temporaryUrl();
        }

        return null;
    }
}
