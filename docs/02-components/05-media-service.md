# Media Service

The MediaService class and MediaManager facade provide programmatic access to media management operations.

## Using the Facade

```php
use CleaniqueCoders\MediaManager\Facades\MediaManager;
```

## Upload Operations

### Upload Single File

```php
$media = MediaManager::upload(
    model: $post,
    file: $request->file('image'),
    collection: 'gallery',
    customProperties: ['alt' => 'Image description']
);
```

### Upload Multiple Files

```php
$media = MediaManager::uploadMultiple(
    model: $post,
    files: $request->file('images'),
    collection: 'gallery'
);
```

### Replace Media

Replace an existing media item with a new file:

```php
MediaManager::replace(
    media: $existingMedia,
    file: $newFile
);
```

## Query Operations

### Browse Media

Get paginated, filtered media:

```php
$media = MediaManager::browse(
    filters: [
        'collection' => 'gallery',
        'type' => 'image',
        'search' => 'photo',
    ],
    perPage: 24
);
```

### Find Media

```php
// Find single media
$media = MediaManager::find($id);

// Find multiple
$media = MediaManager::findMany([1, 2, 3]);
```

### Get Collections

```php
$collections = MediaManager::getCollections();
// Returns: ['default', 'gallery', 'documents', ...]
```

### Get MIME Types

```php
$mimeTypes = MediaManager::getMimeTypes();
// Returns unique MIME types used in the database
```

## Delete Operations

### Delete Single

```php
MediaManager::delete($media);
```

### Delete Multiple

```php
MediaManager::deleteMultiple([1, 2, 3]);
```

## Metadata Operations

### Update Properties

```php
MediaManager::updateProperties(
    media: $media,
    properties: ['alt' => 'New alt text', 'caption' => 'Photo caption']
);
```

### Rename Media

```php
MediaManager::rename(
    media: $media,
    name: 'new-file-name'
);
```

### Reorder Media

```php
MediaManager::reorder(
    model: $post,
    collection: 'gallery',
    orderedIds: [3, 1, 2, 5, 4]
);
```

## Temporary Uploads

For handling uploads before the model exists:

### Store Temporary

```php
$tempId = MediaManager::storeTemporary(
    file: $uploadedFile,
    disk: 'local'
);
```

### Attach Temporary to Model

```php
MediaManager::attachTemporary(
    model: $post,
    tempId: $tempId,
    collection: 'gallery'
);
```

### Cleanup Old Temporary Files

```php
MediaManager::cleanupTemporary(
    olderThanHours: 24
);
```

## Utility Methods

### Format File Size

```php
$formatted = MediaManager::formatFileSize(1048576);
// Returns: "1 MB"
```

### Get File Type Category

```php
$category = MediaManager::getFileTypeCategory('image/jpeg');
// Returns: "image"

$category = MediaManager::getFileTypeCategory('application/pdf');
// Returns: "pdf"
```

### Validate File

```php
$errors = MediaManager::validateFile(
    file: $uploadedFile,
    maxSize: 10 * 1024 * 1024, // 10MB
    allowedMimes: ['image/jpeg', 'image/png']
);

if (empty($errors)) {
    // File is valid
}
```

## Available Filters

When using `browse()`, these filters are available:

| Filter | Description |
|--------|-------------|
| `search` | Search in name, file_name, and custom_properties |
| `collection` | Filter by collection_name |
| `mime_type` | Filter by MIME type |
| `type` | Filter by category (image, video, audio, pdf, document) |
| `date_from` | Filter by created_at >= date |
| `date_to` | Filter by created_at <= date |
| `model_type` | Filter by model class |
| `model_id` | Filter by model instance |

## Service Class Direct Usage

If you prefer dependency injection:

```php
use CleaniqueCoders\MediaManager\MediaService;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    public function index()
    {
        $media = $this->mediaService->browse(['type' => 'image'], 24);
        return view('media.index', compact('media'));
    }
}
```

## Related Documentation

- [Traits](06-traits.md) - Model traits for media management
- [Configuration](../03-configuration/README.md) - Service configuration
