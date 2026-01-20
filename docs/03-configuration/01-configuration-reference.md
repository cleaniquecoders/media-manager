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
    'routes' => [
        'enabled' => true,
        'prefix' => 'media-manager',
        'middleware' => ['web', 'auth'],
    ],

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
        'chunk_size' => 1024 * 1024, // 1MB chunks
    ],

    'browser' => [
        'default_view' => 'grid',
        'items_per_page' => 24,
        'columns' => 4,
    ],

    'authorization' => [
        'gate' => null,
    ],

    'temporary_disk' => 'local',
    'temporary_upload_expiration' => 24,
];
```

## Route Configuration

Control the media browser route behavior.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enabled` | bool | `true` | Enable/disable the browser route |
| `prefix` | string | `'media-manager'` | URL prefix for the route |
| `middleware` | array | `['web', 'auth']` | Middleware to apply |

### Examples

**Change the URL prefix:**

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'admin/media',
    'middleware' => ['web', 'auth'],
],
```

**Add admin middleware:**

```php
'routes' => [
    'enabled' => true,
    'prefix' => 'media-manager',
    'middleware' => ['web', 'auth', 'admin'],
],
```

**Disable the route entirely:**

```php
'routes' => [
    'enabled' => false,
],
```

## Upload Configuration

Control file upload behavior and restrictions.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `max_file_size` | int | `10485760` (10MB) | Maximum file size in bytes |
| `allowed_mimes` | array | See below | Allowed MIME types |
| `chunk_size` | int | `1048576` (1MB) | Chunk size for uploads |

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

## Authorization Configuration

Control access to the media manager.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `gate` | string\|null | `null` | Gate name for authorization |

When `gate` is `null`, all authenticated users can access the media manager.

See [Authorization](02-authorization.md) for detailed setup.

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
'routes' => [
    'enabled' => env('MEDIA_MANAGER_ROUTES_ENABLED', true),
    'prefix' => env('MEDIA_MANAGER_PREFIX', 'media-manager'),
],

'upload' => [
    'max_file_size' => env('MEDIA_MANAGER_MAX_FILE_SIZE', 10 * 1024 * 1024),
],

'authorization' => [
    'gate' => env('MEDIA_MANAGER_GATE'),
],
```

## Related Documentation

- [Authorization](02-authorization.md) - Access control setup
- [Installation](../01-getting-started/01-installation.md) - Initial setup
