<?php

use CleaniqueCoders\MediaManager\MediaManager;
use CleaniqueCoders\MediaManager\Services\MediaService;
use CleaniqueCoders\MediaManager\Support\MediaFilter;
use CleaniqueCoders\MediaManager\Tests\Fixtures\CustomMedia;
use CleaniqueCoders\MediaManager\Tests\Fixtures\TestPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->service = new MediaService(new MediaFilter);
});

it('resolves the default spatie media model', function () {
    expect(MediaManager::mediaModel())->toBe(Media::class);
});

it('resolves a custom media model from config', function () {
    config()->set('media-library.media_model', CustomMedia::class);

    expect(MediaManager::mediaModel())->toBe(CustomMedia::class);
});

it('queries media through the configured custom model', function () {
    config()->set('media-library.media_model', CustomMedia::class);

    $post = TestPost::create(['title' => 'Test Post']);
    $this->service->upload($post, UploadedFile::fake()->image('test.jpg', 100, 100), 'gallery');

    $paginator = $this->service->getMedia();

    expect($paginator->total())->toBe(1);
    expect($paginator->items()[0])->toBeInstanceOf(CustomMedia::class);
});

it('queries collections through the configured custom model', function () {
    config()->set('media-library.media_model', CustomMedia::class);

    $mediaModel = MediaManager::mediaModel();
    expect($mediaModel::query()->getModel())->toBeInstanceOf(CustomMedia::class);

    $post = TestPost::create(['title' => 'Test Post']);
    $this->service->upload($post, UploadedFile::fake()->image('test.jpg', 100, 100), 'gallery');

    expect($this->service->getCollections()->all())->toContain('gallery');
});
