# Traits

Media Manager provides several traits and concerns to extend your models and components.

## HasMediaManager Trait

The main trait for adding media management capabilities to your Eloquent models.

### Setup

```php
<?php

namespace App\Models;

use CleaniqueCoders\MediaManager\Concerns\HasMediaManager;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Post extends Model implements HasMedia
{
    use HasMediaManager;
}
```

### Upload Methods

```php
// Upload single file
$media = $post->uploadMedia($file, 'gallery', ['alt' => 'Description']);

// Upload multiple files
$media = $post->uploadMediaMultiple($files, 'gallery');

// Replace all media in collection
$post->replaceMediaInCollection($files, 'gallery');
```

### Query Methods

```php
// Get media ordered by order_column
$media = $post->getMediaOrdered('gallery');

// Get first media or null (not exception)
$media = $post->getFirstMediaOrNull('gallery');

// Check if collection has any media
if ($post->hasMediaInCollection('gallery')) {
    // ...
}

// Count media in collection
$count = $post->getMediaCount('gallery');
```

### URL Methods

```php
// Get URL for first media in collection
$url = $post->getMediaUrl('featured');

// Get all URLs in collection
$urls = $post->getMediaUrls('gallery');
// Returns: ['https://...', 'https://...', ...]
```

### Management Methods

```php
// Clear entire collection
$post->clearMediaInCollection('gallery');

// Reorder media by IDs
$post->reorderMedia([3, 1, 2], 'gallery');

// Keep only specified media IDs, remove others
$post->syncMedia([1, 2, 3], 'gallery');

// Attach existing media from another location
$post->attachExistingMedia($mediaId, 'gallery');
```

### Collection Information

```php
// Get names of collections that have media
$collections = $post->getCollectionsWithMedia();
// Returns: ['featured', 'gallery', 'documents']

// Get media grouped by collection
$grouped = $post->getMediaGroupedByCollection();
// Returns: ['featured' => [...], 'gallery' => [...]]
```

## HandlesUpload Concern

Used internally by upload components. You can use it in custom components.

```php
use CleaniqueCoders\MediaManager\Concerns\HandlesUpload;

class MyUploadComponent extends Component
{
    use HandlesUpload;

    public function uploadFile($file)
    {
        // Validate the file
        $errors = $this->validateUploadedFile($file);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addError('file', $error);
            }
            return;
        }

        // Get accepted types string for HTML input
        $accept = $this->getAcceptedTypesString();

        // Get max size in MB for display
        $maxMb = $this->getMaxFileSizeInMb();

        // Format bytes for display
        $size = $this->formatFileSize($file->getSize());

        // Get type category
        $type = $this->getFileTypeFromMime($file->getMimeType());

        // Check file type
        if ($this->isImage($file)) {
            // Handle image
        }
    }
}
```

### Available Methods

| Method | Description |
|--------|-------------|
| `validateUploadedFile($file)` | Validate against config constraints |
| `getAcceptedTypesString()` | Get MIME types for HTML accept attribute |
| `getMaxFileSizeInMb()` | Get max size in MB |
| `formatFileSize($bytes)` | Format bytes for display |
| `getFileTypeFromMime($mime)` | Get category from MIME type |
| `isImage($file)` | Check if file is image |
| `isVideo($file)` | Check if file is video |
| `isPdf($file)` | Check if file is PDF |
| `getPreviewUrl($file)` | Get temporary preview URL |

## HandlesPreview Concern

Format media data for display in UI components.

```php
use CleaniqueCoders\MediaManager\Concerns\HandlesPreview;

class MyPreviewComponent extends Component
{
    use HandlesPreview;

    public function getMediaForDisplay($media)
    {
        // Get formatted preview data
        $data = $this->getPreviewData($media);

        // Get thumbnail URL with fallback
        $thumb = $this->getThumbnailUrl($media);

        // Get type category
        $type = $this->getMediaType($media);

        // Check types
        if ($this->isMediaImage($media)) {
            // ...
        }

        // Check if browser can preview
        if ($this->canPreview($media)) {
            // Show inline preview
        }

        // Get icon name for type
        $icon = $this->getMediaIcon($media);
    }
}
```

### Preview Data Structure

`getPreviewData()` returns:

```php
[
    'id' => 123,
    'name' => 'photo',
    'file_name' => 'photo.jpg',
    'mime_type' => 'image/jpeg',
    'size' => 1048576,
    'size_formatted' => '1 MB',
    'url' => 'https://...',
    'thumbnail_url' => 'https://...',
    'type' => 'image',
    'collection' => 'gallery',
    'custom_properties' => ['alt' => '...'],
    'created_at' => '2024-01-15 10:30:00',
    'updated_at' => '2024-01-15 10:30:00',
]
```

### Available Methods

| Method | Description |
|--------|-------------|
| `getPreviewData($media)` | Get full preview data array |
| `getThumbnailUrl($media)` | Get thumbnail with fallback |
| `getMediaType($media)` | Get type category |
| `isMediaImage($media)` | Check if image |
| `isMediaVideo($media)` | Check if video |
| `isMediaAudio($media)` | Check if audio |
| `isMediaPdf($media)` | Check if PDF |
| `canPreview($media)` | Check if previewable in browser |
| `getMediaIcon($media)` | Get icon name for file type |
| `formatBytes($bytes)` | Format file size |

## Related Documentation

- [Model Setup](../01-getting-started/03-model-setup.md) - Configure models
- [Media Service](05-media-service.md) - Programmatic API
