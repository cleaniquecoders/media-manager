# Media Uploader

The Media Uploader component provides a drag-and-drop interface for uploading files to a specific model and collection.

![Media Uploader](../../assets/media-manager-basic.png)

## Basic Usage

```blade
<livewire:media-manager::uploader
    :model="$post"
    collection="gallery"
/>
```

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `model` | Model | required | The model to attach media to |
| `collection` | string | `'default'` | Collection name |
| `maxFiles` | int | `null` | Maximum number of files (null = unlimited) |
| `acceptedTypes` | array | config value | Allowed MIME types |
| `withProperties` | array | `[]` | Custom properties to capture |

## Examples

### Single File Upload

For a featured image that accepts only one file:

```blade
<livewire:media-manager::uploader
    :model="$post"
    collection="featured"
    :max-files="1"
    :accepted-types="['image/jpeg', 'image/png', 'image/webp']"
/>
```

### Multiple Files with Properties

Upload gallery images with alt text:

```blade
<livewire:media-manager::uploader
    :model="$post"
    collection="gallery"
    :max-files="10"
    :with-properties="['alt', 'caption']"
/>
```

### Document Uploads

Upload PDF documents:

```blade
<livewire:media-manager::uploader
    :model="$post"
    collection="attachments"
    :accepted-types="['application/pdf']"
/>
```

## Features

### Drag and Drop

Users can drag files directly onto the upload zone.

### File Validation

Files are validated against:

- Maximum file size (configurable)
- Allowed MIME types
- Maximum file count

Invalid files display error messages.

### Progress Tracking

Upload progress is shown for each file.

### Existing Media Display

The component shows media already in the collection with options to remove.

### Custom Properties

When `withProperties` is set, input fields appear for each property:

```blade
<livewire:media-manager::uploader
    :model="$post"
    collection="gallery"
    :with-properties="['alt' => 'Alt Text', 'caption' => 'Caption']"
/>
```

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| `media-uploaded` | `['media' => [...]]` | After successful upload |
| `media-removed` | `['id' => $id]` | After removing existing media |
| `upload:progress` | `['progress' => $percent]` | Upload progress updates |

## Listening for Events

In your parent component:

```php
#[On('media-uploaded')]
public function handleMediaUploaded(array $media): void
{
    // Handle the uploaded media
}
```

Or in JavaScript:

```javascript
Livewire.on('media-uploaded', (data) => {
    console.log('Uploaded:', data.media);
});
```

## Related Components

- [Media Collection](03-media-collection.md) - Edit media inline
- [Media Browser](01-media-browser.md) - Browse all media
