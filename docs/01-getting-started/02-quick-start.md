# Quick Start

Get Media Manager running in your application in minutes.

## Step 1: Access the Media Browser

After installation, visit the media browser at:

```text
/media-manager
```

This displays the full-featured media browser interface where you can:

- View all uploaded media in grid or list view
- Filter by collection, type, or date
- Search media by name
- Preview and delete files

![Media Browser Interface](../../assets/media-manager-browser.png)

## Step 2: Add Media Support to Your Model

To upload media to a model, implement the `HasMedia` interface and use the `HasMediaManager` trait:

```php
<?php

namespace App\Models;

use CleaniqueCoders\MediaManager\Concerns\HasMediaManager;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class Post extends Model implements HasMedia
{
    use HasMediaManager;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }
}
```

## Step 3: Add the Upload Component

In your Blade view, add the media uploader component:

```blade
<livewire:media-manager::uploader
    :model="$post"
    collection="gallery"
    :max-files="10"
/>
```

![Media Uploader](../../assets/media-manager-basic.png)

## Step 4: Display Media in Collections

Use the collection component to display and manage media inline:

```blade
<livewire:media-manager::collection
    :model="$post"
    collection="gallery"
    sortable
/>
```

![Media Collection](../../assets/media-manager-collection.png)

## Using the Facade

You can also manage media programmatically using the facade:

```php
use CleaniqueCoders\MediaManager\Facades\MediaManager;

// Upload a file
MediaManager::upload($post, $request->file('image'), 'gallery');

// Upload multiple files
MediaManager::uploadMultiple($post, $request->file('images'), 'gallery');

// Get media with filters
$media = MediaManager::browse(['collection' => 'gallery'], perPage: 24);

// Delete media
MediaManager::delete($media);
```

## Next Steps

- [Model Setup](03-model-setup.md) - Learn more about configuring models
- [Components](../02-components/README.md) - Explore all available components
- [Configuration](../03-configuration/README.md) - Customize the package behavior
