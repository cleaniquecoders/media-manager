# Changelog

All notable changes to `media-manager` will be documented in this file.

## Fix Livewire 3 & 4 Usage - 2026-01-20

### Release Notes - v1.1.0

#### Livewire 4 Support & Component-First Architecture

This release introduces Livewire 4 support and restructures the package to follow a component-first architecture where users control their own routes and layouts.

##### New Features

###### Livewire 4 Support

- Added support for Livewire 4 with `addNamespace()` component registration
- Configurable Livewire version via `config/media-manager.php`:

```php
'livewire' => 'v4', // or 'v3' for Livewire 3

```
###### Component-First Architecture

- All Livewire components are now standalone and can be embedded in any view
- Users create their own routes and use their own layouts
- More flexible integration with existing applications

##### Breaking Changes

###### Routes Removed

The built-in `/media-manager` route has been removed. Create your own route and view:

```php
// routes/web.php
Route::get('/media', function () {
    return view('media');
})->middleware(['web', 'auth']);

```
```blade
{{-- resources/views/media.blade.php --}}
<x-app-layout>
    <livewire:media-manager::browser />
</x-app-layout>

```
###### Component Class Renamed

Internal Livewire component classes have been renamed for Livewire 4 compatibility:

| Old Class | New Class |
|-----------|-----------|
| MediaBrowser | Browser |
| MediaUploader | Uploader |
| MediaCollection | Collection |
| MediaPicker | Picker |

> **Note:** Component usage in Blade templates remains unchanged:

```blade
<livewire:media-manager::browser />
<livewire:media-manager::uploader />
<livewire:media-manager::collection />
<livewire:media-manager::picker />

```
###### Configuration Changes

**Removed options:**

- `routes.enabled` - Routes no longer provided
- `routes.prefix` - Routes no longer provided
- `routes.middleware` - Routes no longer provided
- `upload.chunk_size` - Not used
- `authorization.gate` - Middleware removed with routes

**New options:**

- `livewire` - Set to `'v4'` (default) or `'v3'`

###### Updated Configuration

```php
<?php

return [
    'livewire' => 'v4',

    'upload' => [
        'max_file_size' => 10 * 1024 * 1024,
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
##### Migration Guide

###### From v1.0.x to v1.1.0

1. Update configuration file:

```bash
php artisan vendor:publish --tag="media-manager-config" --force

```
2. Create your own route for the browser:

```php
// routes/web.php
Route::get('/media', function () {
    return view('media');
})->middleware(['web', 'auth']);

```
3. Create the view with your layout:

```blade
{{-- resources/views/media.blade.php --}}
<x-app-layout>
    <livewire:media-manager::browser />
</x-app-layout>

```
4. If using Livewire 3, update your config:

```php
'livewire' => 'v3',

```
##### Requirements

- PHP 8.2+
- Laravel 11.0+
- Livewire 3.0+ or 4.0+

##### Dependencies

- `livewire/livewire`: ^3.0 || ^4.0
- `spatie/laravel-medialibrary`: ^11.0

## FIx Livewire 4 Support - 2026-01-20

### Release Notes - v1.0.1

#### Livewire 3 & 4 Compatibility Fix

##### What's Changed

This release fixes Livewire component registration to work seamlessly with both Livewire 3 and Livewire 4.

##### Problem

The previous implementation used `Livewire::component('media-manager::browser', MediaBrowser::class)` syntax which doesn't
work correctly in Livewire 4. Livewire 4 introduced `addNamespace()` for registering package components with the
`vendor::component` syntax.

##### Solution

- **Renamed Livewire component classes** to shorter names that match the component alias:
  
  - `MediaBrowser` → `Browser`
  - `MediaUploader` → `Uploader`
  - `MediaCollection` → `Collection`
  - `MediaPicker` → `Picker`
  
- **Added version detection** in the service provider to automatically use the appropriate registration method:
  
  - **Livewire 4**: Uses `Livewire::addNamespace()` for automatic component discovery
  - **Livewire 3**: Falls back to explicit `Livewire::component()` registration
  

##### Usage

Component usage remains the same across both Livewire versions:

  ```blade
  <livewire:media-manager::browser />                                                                                         
<livewire:media-manager::uploader :model="$model" collection="avatar" />                                                    
<livewire:media-manager::collection :model="$model" collection="gallery" />                                                 
<livewire:media-manager::picker />                                                                                          


  ```
##### Breaking Changes

None. The component names used in Blade templates remain unchanged.

##### Upgrade Guide

No action required. Simply update to v1.0.1:

```bash
  composer update cleaniquecoders/media-manager                                                                               


```
## First Release - 2026-01-20

### Release Notes - v1.0.0

#### 🎉 Initial Release

We're excited to announce the first stable release of **Media Manager** - a Laravel package for managing media built on top of Spatie Media Library.

<img width="1250" height="518" alt="Screenshot 2026-01-20 at 1 20 07 PM" src="https://github.com/user-attachments/assets/c3097438-f3b3-4cc0-8d92-780584e374cf" />
##### Features
- **Media Browser** - Browse and manage uploaded media files
- **Media Uploader** - Upload single or multiple files with drag-and-drop support
- **Media Collection** - Organize media into collections
- **Media Picker** - Select media for association with models
- **Media Service** - Programmatic media management API
- **Authorization** - Configurable access control for media operations
##### Requirements

- PHP 8.2+
- Laravel 11.x / 12.x
- Livewire 3.x & 4.x

##### Installation

```bash
  composer require cleaniquecoders/media-manager                                                                



```
##### Documentation

For detailed usage and configuration, please refer to the [docs/](https://github.com/cleaniquecoders/media-manager/tree/main/docs).
