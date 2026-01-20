# Media Picker

The Media Picker component provides a modal interface for browsing and selecting existing media.

## Basic Usage

```blade
<livewire:media-manager::picker wire:model="selectedMediaIds" />
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `selectedIds` | array | `[]` | Currently selected media IDs (modelable) |
| `multiple` | bool | `false` | Allow multiple selections |
| `collection` | string | `''` | Filter by collection |
| `type` | string | `''` | Filter by file type |

## Examples

### Single Selection

Select one media item:

```blade
<livewire:media-manager::picker
    wire:model="featuredImageId"
/>
```

### Multiple Selection

Select multiple items:

```blade
<livewire:media-manager::picker
    wire:model="galleryImageIds"
    multiple
/>
```

### Filtered by Collection

Only show media from a specific collection:

```blade
<livewire:media-manager::picker
    wire:model="selectedIds"
    collection="images"
    multiple
/>
```

### Filtered by Type

Only show images:

```blade
<livewire:media-manager::picker
    wire:model="selectedIds"
    type="image"
    multiple
/>
```

## Features

### Modal Interface

The picker opens in a modal overlay, keeping users on the current page.

### Search and Filter

Users can search and filter media within the picker:

- Search by file name
- Filter by collection
- Filter by file type

### Preview Selected

Selected items are highlighted and shown in a preview area.

### Single vs Multiple Mode

- **Single**: Selecting a new item replaces the previous selection
- **Multiple**: Toggle items on/off, select multiple items

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `media-selected` | `['ids' => [...]]` | When selection is confirmed |

## Integration Example

In a parent Livewire component:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class PostEditor extends Component
{
    public array $galleryIds = [];

    #[On('media-selected')]
    public function handleMediaSelected(array $ids): void
    {
        $this->galleryIds = $ids;
    }

    public function render()
    {
        return view('livewire.post-editor');
    }
}
```

```blade
{{-- resources/views/livewire/post-editor.blade.php --}}
<div>
    <h3>Gallery Images</h3>

    <livewire:media-manager::picker
        wire:model="galleryIds"
        multiple
        type="image"
    />

    <p>Selected: {{ count($galleryIds) }} images</p>
</div>
```

## Opening the Picker Programmatically

Dispatch an event to open the picker:

```javascript
Livewire.dispatch('open-media-picker');
```

Or from a Livewire component:

```php
$this->dispatch('open-media-picker');
```

## Customization

Publish and edit the view:

```bash
php artisan vendor:publish --tag="media-manager-views"
```

Edit `resources/views/vendor/media-manager/livewire/media-picker.blade.php`.

## Related Components

- [Media Browser](01-media-browser.md) - Full browsing interface
- [Media Uploader](02-media-uploader.md) - Upload new media
