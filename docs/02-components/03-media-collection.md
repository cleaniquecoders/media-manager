# Media Collection

The Media Collection component provides an inline editor for managing media within a collection,
including drag-and-drop reordering.

![Media Collection](../../assets/media-manager-collection.png)

## Basic Usage

```blade
<livewire:media-manager::collection
    :model="$post"
    collection="gallery"
/>
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `model` | Model | required | The model with media |
| `collection` | string | `'default'` | Collection name |
| `sortable` | bool | `false` | Enable drag-and-drop reordering |
| `maxFiles` | int | `null` | Maximum files allowed |
| `acceptedTypes` | array | config value | Allowed MIME types |
| `withProperties` | array | `[]` | Editable custom properties |

## Examples

### Sortable Gallery

```blade
<livewire:media-manager::collection
    :model="$post"
    collection="gallery"
    sortable
    :max-files="20"
/>
```

### With Editable Properties

```blade
<livewire:media-manager::collection
    :model="$post"
    collection="gallery"
    sortable
    :with-properties="['alt', 'caption']"
/>
```

### Document Collection

```blade
<livewire:media-manager::collection
    :model="$post"
    collection="documents"
    :accepted-types="['application/pdf']"
    :with-properties="['title', 'description']"
/>
```

## Features

### Drag and Drop Reordering

When `sortable` is enabled, users can drag media items to reorder them. The order is persisted automatically.

Uses [SortableJS](https://sortablejs.github.io/Sortable/) for smooth drag-and-drop.

### Inline Editing

Click the edit button on any media item to:

- Rename the file
- Edit custom properties
- View file details

### Upload Zone Toggle

The upload zone can be shown/hidden to keep the interface clean when not adding new files.

### Remove Media

Remove individual media items with confirmation.

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `media-uploaded` | `['media' => [...]]` | After upload |
| `media-removed` | `['id' => $id]` | After removal |
| `media-updated` | `['id' => $id]` | After property update |
| `media-reordered` | `['ids' => [...]]` | After reorder |

## Methods

If you need to interact with the component from a parent:

```php
// In parent component
$this->dispatch('refresh-collection')->to('media-manager::collection');
```

## Styling

The component uses Tailwind CSS classes. Customize by publishing and editing views:

```bash
php artisan vendor:publish --tag="media-manager-views"
```

Edit `resources/views/vendor/media-manager/livewire/media-collection.blade.php`.

## Related Components

- [Media Uploader](02-media-uploader.md) - Simpler upload-only component
- [Media Browser](01-media-browser.md) - Browse all media
