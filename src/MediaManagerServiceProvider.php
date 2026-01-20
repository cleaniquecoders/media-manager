<?php

namespace CleaniqueCoders\MediaManager;

use CleaniqueCoders\MediaManager\Commands\MediaManagerCommand;
use CleaniqueCoders\MediaManager\Livewire\Browser;
use CleaniqueCoders\MediaManager\Livewire\Collection;
use CleaniqueCoders\MediaManager\Livewire\Picker;
use CleaniqueCoders\MediaManager\Livewire\Uploader;
use CleaniqueCoders\MediaManager\Services\MediaService;
use CleaniqueCoders\MediaManager\Support\MediaFilter;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasCommand(MediaManagerCommand::class);
    }

    public function packageBooted(): void
    {
        $this->registerLivewireComponents();
        $this->registerPublishables();
    }

    protected function registerPublishables(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/media-manager'),
            ], 'media-manager-views');
        }
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(MediaFilter::class, function () {
            return new MediaFilter;
        });

        $this->app->singleton(MediaService::class, function ($app) {
            return new MediaService($app->make(MediaFilter::class));
        });

        $this->app->singleton(MediaManager::class, function ($app) {
            return new MediaManager($app->make(MediaService::class));
        });
    }

    protected function registerLivewireComponents(): void
    {
        $version = config('media-manager.livewire', 'v4');

        if ($this->shouldUseLivewire4($version)) {
            Livewire::addNamespace('media-manager', classNamespace: 'CleaniqueCoders\MediaManager\Livewire');
        } else {
            Livewire::component('media-manager::browser', Browser::class);
            Livewire::component('media-manager::uploader', Uploader::class);
            Livewire::component('media-manager::collection', Collection::class);
            Livewire::component('media-manager::picker', Picker::class);
        }
    }

    protected function shouldUseLivewire4(string $version): bool
    {
        return match ($version) {
            'v4' => true,
            'v3' => false,
            default => method_exists(Livewire::getFacadeRoot(), 'addNamespace'),
        };
    }
}
