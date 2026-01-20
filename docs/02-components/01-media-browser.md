# Media Browser

The Media Browser component provides a full-featured interface for browsing, filtering, and managing all media in your application.

![Media Browser Interface](../../assets/media-manager-browser.png)

## Basic Usage

Add the media browser to any Blade view with your own layout:

```blade
{{-- resources/views/media.blade.php --}}
<x-app-layout>
    <livewire:media-manager::browser />
</x-app-layout>
```

Create a route to access the browser:

```php
// routes/web.php
Route::get('/media', function () {
    return view('media');
})->middleware(['web', 'auth']);
```

## Features

### View Modes

Switch between grid and list views:

- **Grid View**: Visual card layout showing thumbnails
- **List View**: Table layout with detailed information

### Filtering

Filter media by:

- **Search**: Search by file name
- **Collection**: Filter by collection name
- **Type**: Filter by file type (image, video, document, etc.)
- **Date Range**: Filter by upload date

### Bulk Operations

Select multiple items and:

- Delete selected media (with confirmation)
- Select all / Deselect all

### Preview Panel

Click on any media item to open a detailed preview showing:

- Full-size preview (images, videos, PDFs)
- File information (size, type, dimensions)
- Custom properties
- Upload date

## Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `view` | string | `'grid'` | Current view mode (`grid` or `list`) |
| `search` | string | `''` | Search query |
| `collection` | string | `''` | Collection filter |
| `type` | string | `''` | Type filter |
| `dateFrom` | string | `''` | Date range start |
| `dateTo` | string | `''` | Date range end |
| `perPage` | int | `24` | Items per page |

## Events

The browser dispatches these Livewire events:

| Event | Payload | Description |
|-------|---------|-------------|
| `media-deleted` | `['ids' => [...]]` | After media deletion |

## Customization

### Override the View

Publish the views and edit the browser template:

```bash
php artisan vendor:publish --tag="media-manager-views"
```

Then edit `resources/views/vendor/media-manager/livewire/media-browser.blade.php`.

### Change Default View Mode

In your config file (`config/media-manager.php`):

```php
'browser' => [
    'default_view' => 'list', // or 'grid'
    'items_per_page' => 24,
    'columns' => 4,
],
```

## Related Components

- [Media Uploader](02-media-uploader.md) - Upload new media
- [Media Picker](04-media-picker.md) - Select existing media
