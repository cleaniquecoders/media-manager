# Configuration Reference

Complete reference for all Media Manager configuration options.

## Publishing Configuration

```bash
php artisan vendor:publish --tag="media-manager-config"
```

## Full Configuration File

```php
<?php

// config/media-manager.php
return [
    'livewire' => 'v4',

    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'video/mp4',
            'video/webm',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],

    'browser' => [
        'default_view' => 'grid',
        'items_per_page' => 24,
        'columns' => 4,
    ],

    'temporary_disk' => 'local',
    'temporary_upload_expiration' => 24,
];
```

## Livewire Configuration

Configure which Livewire version registration method to use.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `livewire` | string | `'v4'` | Livewire version (`v4` or `v3`) |

### Examples

**Use Livewire 4 (default):**

```php
'livewire' => 'v4',
```

**Use Livewire 3:**

```php
'livewire' => 'v3',
```

> **Note**: Livewire 4 uses `addNamespace()` for component registration.
> Livewire 3 uses explicit `component()` registration.

## Upload Configuration

Control file upload behavior and restrictions.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `max_file_size` | int | `10485760` (10MB) | Maximum file size in bytes |
| `allowed_mimes` | array | See below | Allowed MIME types |

### Default Allowed MIME Types

**Images:**

- `image/jpeg`
- `image/png`
- `image/gif`
- `image/webp`
- `image/svg+xml`

**Videos:**

- `video/mp4`
- `video/webm`

**Documents:**

- `application/pdf`
- `application/msword` (.doc)
- `application/vnd.openxmlformats-officedocument.wordprocessingml.document` (.docx)
- `application/vnd.ms-excel` (.xls)
- `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` (.xlsx)

### Examples

**Increase file size limit to 50MB:**

```php
'upload' => [
    'max_file_size' => 50 * 1024 * 1024,
    // ...
],
```

**Allow only images:**

```php
'upload' => [
    'max_file_size' => 10 * 1024 * 1024,
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ],
],
```

**Add audio support:**

```php
'upload' => [
    'allowed_mimes' => [
        // existing types...
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
    ],
],
```

## Browser Configuration

Customize the media browser interface.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `default_view` | string | `'grid'` | Default view mode (`grid` or `list`) |
| `items_per_page` | int | `24` | Items per page in pagination |
| `columns` | int | `4` | Grid columns (affects layout) |

### Examples

**Use list view by default with more items:**

```php
'browser' => [
    'default_view' => 'list',
    'items_per_page' => 50,
    'columns' => 4,
],
```

## Temporary Upload Configuration

Configure temporary file handling.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `temporary_disk` | string | `'local'` | Disk for temporary uploads |
| `temporary_upload_expiration` | int | `24` | Hours before cleanup |

### Examples

**Use S3 for temporary storage:**

```php
'temporary_disk' => 's3',
'temporary_upload_expiration' => 12,
```

## Environment Variables

You can use environment variables for sensitive or environment-specific settings:

```php
'livewire' => env('MEDIA_MANAGER_LIVEWIRE_VERSION', 'v4'),

'upload' => [
    'max_file_size' => env('MEDIA_MANAGER_MAX_FILE_SIZE', 10 * 1024 * 1024),
],
```

## Related Documentation

- [Installation](../01-getting-started/01-installation.md) - Initial setup
- [Components](../02-components/README.md) - Livewire components
