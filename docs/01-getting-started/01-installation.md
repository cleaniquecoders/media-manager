# Installation

This guide covers the installation process for Media Manager.

## Requirements

- PHP 8.2 or higher
- Laravel 10.x or 11.x
- Livewire 3.x
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)

## Install via Composer

```bash
composer require cleaniquecoders/media-manager
```

## Run the Install Command

The package provides an install command that guides you through setup:

```bash
php artisan media-manager:install
```

This command will:

1. Publish the configuration file
2. Optionally publish views for customization
3. Run Spatie Media Library migrations (if not already done)
4. Display usage examples

## Manual Setup

If you prefer manual setup, follow these steps:

### Publish Configuration

```bash
php artisan vendor:publish --tag="media-manager-config"
```

### Publish Migrations

```bash
php artisan vendor:publish --tag="media-manager-migrations"
php artisan migrate
```

### Publish Views (Optional)

```bash
php artisan vendor:publish --tag="media-manager-views"
```

## Spatie Media Library Setup

If you haven't already set up Spatie Media Library, run:

```bash
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate
```

## Verify Installation

After installation, verify the package is working:

```bash
php artisan route:list | grep media-manager
```

You should see the media manager route listed.

## Next Steps

- [Quick Start](02-quick-start.md) - Get your first media interface running
- [Model Setup](03-model-setup.md) - Configure your models
