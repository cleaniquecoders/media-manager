<?php

namespace CleaniqueCoders\MediaManager;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use CleaniqueCoders\MediaManager\Commands\MediaManagerCommand;

class MediaManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('media-manager')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_media_manager_table')
            ->hasCommand(MediaManagerCommand::class);
    }
}
