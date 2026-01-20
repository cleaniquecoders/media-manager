# Changelog

All notable changes to `media-manager` will be documented in this file.

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
