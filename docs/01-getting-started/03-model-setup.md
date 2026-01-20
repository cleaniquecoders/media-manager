# Model Setup

Learn how to configure your Eloquent models to work with Media Manager.

## Basic Setup

Your model must implement the `HasMedia` interface and use the `HasMediaManager` trait:

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

## Defining Media Collections

Register media collections to organize your files:

```php
public function registerMediaCollections(): void
{
    // Single file collection (e.g., featured image)
    $this->addMediaCollection('featured')
        ->singleFile();

    // Multiple files collection
    $this->addMediaCollection('gallery');

    // Collection with constraints
    $this->addMediaCollection('documents')
        ->acceptsMimeTypes(['application/pdf']);
}
```

## Available Trait Methods

The `HasMediaManager` trait provides these methods:

### Upload Methods

```php
// Upload single file
$model->uploadMedia($file, 'collection', ['alt' => 'Description']);

// Upload multiple files
$model->uploadMediaMultiple($files, 'collection');

// Replace all media in collection
$model->replaceMediaInCollection($files, 'collection');
```

### Query Methods

```php
// Get ordered media
$model->getMediaOrdered('collection');

// Get first media or null
$model->getFirstMediaOrNull('collection');

// Check if collection has media
$model->hasMediaInCollection('collection');

// Count media in collection
$model->getMediaCount('collection');
```

### Management Methods

```php
// Clear all media in collection
$model->clearMediaInCollection('collection');

// Reorder media by IDs
$model->reorderMedia([3, 1, 2], 'collection');

// Sync media (keep only specified IDs)
$model->syncMedia([1, 2, 3], 'collection');

// Attach existing media
$model->attachExistingMedia($mediaId, 'collection');
```

### URL Methods

```php
// Get URL for first media
$url = $model->getMediaUrl('collection');

// Get all URLs in collection
$urls = $model->getMediaUrls('collection');
```

### Collection Information

```php
// Get collections that have media
$collections = $model->getCollectionsWithMedia();

// Get media grouped by collection
$grouped = $model->getMediaGroupedByCollection();
```

## Example: Blog Post Model

Here's a complete example for a blog post with multiple media collections:

```php
<?php

namespace App\Models;

use CleaniqueCoders\MediaManager\Concerns\HasMediaManager;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasMediaManager;

    protected $fillable = ['title', 'content', 'published_at'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery');

        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes(['application/pdf']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600);
    }
}
```

## Next Steps

- [Components](../02-components/README.md) - Learn about Livewire components
- [Configuration](../03-configuration/README.md) - Customize settings
