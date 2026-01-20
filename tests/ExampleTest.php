<?php

use CleaniqueCoders\MediaManager\MediaManager;
use CleaniqueCoders\MediaManager\Services\MediaService;
use CleaniqueCoders\MediaManager\Support\MediaFilter;

it('can resolve media manager from container', function () {
    $manager = app(MediaManager::class);

    expect($manager)->toBeInstanceOf(MediaManager::class);
});

it('can resolve media service from container', function () {
    $service = app(MediaService::class);

    expect($service)->toBeInstanceOf(MediaService::class);
});

it('can resolve media filter from container', function () {
    $filter = app(MediaFilter::class);

    expect($filter)->toBeInstanceOf(MediaFilter::class);
});

it('has correct default configuration', function () {
    expect(config('media-manager.livewire'))->toBe('v4');
    expect(config('media-manager.browser.default_view'))->toBe('grid');
    expect(config('media-manager.browser.items_per_page'))->toBe(24);
});

it('facade resolves correctly', function () {
    $manager = \CleaniqueCoders\MediaManager\Facades\MediaManager::getFacadeRoot();

    expect($manager)->toBeInstanceOf(MediaManager::class);
});
