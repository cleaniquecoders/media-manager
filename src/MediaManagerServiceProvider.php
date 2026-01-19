<?php

namespace CleaniqueCoders\MediaManager;

use CleaniqueCoders\MediaManager\Commands\MediaManagerCommand;
use CleaniqueCoders\MediaManager\Http\Middleware\AuthorizeMediaManager;
use CleaniqueCoders\MediaManager\Livewire\MediaBrowser;
use CleaniqueCoders\MediaManager\Livewire\MediaCollection;
use CleaniqueCoders\MediaManager\Livewire\MediaPicker;
use CleaniqueCoders\MediaManager\Livewire\MediaUploader;
use CleaniqueCoders\MediaManager\Services\MediaService;
use CleaniqueCoders\MediaManager\Support\MediaFilter;
use Illuminate\Support\Facades\Route;
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
        $this->registerRoutes();
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
        Livewire::component('media-manager::browser', MediaBrowser::class);
        Livewire::component('media-manager::uploader', MediaUploader::class);
        Livewire::component('media-manager::collection', MediaCollection::class);
        Livewire::component('media-manager::picker', MediaPicker::class);
    }

    protected function registerRoutes(): void
    {
        if (! config('media-manager.routes.enabled', true)) {
            return;
        }

        Route::middleware(array_merge(
            config('media-manager.routes.middleware', ['web', 'auth']),
            [AuthorizeMediaManager::class]
        ))
            ->prefix(config('media-manager.routes.prefix', 'media-manager'))
            ->group(function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
            });
    }
}
