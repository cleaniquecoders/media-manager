<?php

use CleaniqueCoders\MediaManager\Services\MediaService;
use CleaniqueCoders\MediaManager\Support\MediaFilter;
use CleaniqueCoders\MediaManager\Tests\Fixtures\TestPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->service = new MediaService(new MediaFilter);
});

it('can upload a file to a model', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    $media = $this->service->upload($post, $file, 'gallery');

    expect($media)->not->toBeNull();
    expect($media->collection_name)->toBe('gallery');
    expect($post->getMedia('gallery'))->toHaveCount(1);
});

it('can upload multiple files to a model', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $files = [
        UploadedFile::fake()->image('test1.jpg', 100, 100),
        UploadedFile::fake()->image('test2.jpg', 100, 100),
        UploadedFile::fake()->image('test3.jpg', 100, 100),
    ];

    $uploaded = $this->service->uploadMultiple($post, $files, 'gallery');

    expect($uploaded)->toHaveCount(3);
    expect($post->getMedia('gallery'))->toHaveCount(3);
});

it('can rename media', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->service->upload($post, $file, 'gallery');

    $this->service->rename($media, 'New Name');

    expect($media->fresh()->name)->toBe('New Name');
});

it('can update custom properties', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->service->upload($post, $file, 'gallery');

    $this->service->updateProperties($media, [
        'alt' => 'Alt text',
        'caption' => 'A caption',
    ]);

    $media->refresh();
    expect($media->getCustomProperty('alt'))->toBe('Alt text');
    expect($media->getCustomProperty('caption'))->toBe('A caption');
});

it('can delete media', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->service->upload($post, $file, 'gallery');
    $mediaId = $media->id;

    $this->service->delete($media);

    expect(\Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId))->toBeNull();
});

it('can delete multiple media items', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $files = [
        UploadedFile::fake()->image('test1.jpg', 100, 100),
        UploadedFile::fake()->image('test2.jpg', 100, 100),
    ];
    $uploaded = $this->service->uploadMultiple($post, $files, 'gallery');
    $ids = $uploaded->pluck('id')->toArray();

    $deleted = $this->service->deleteMultiple($ids);

    expect($deleted)->toBe(2);
    expect($post->getMedia('gallery'))->toHaveCount(0);
});

it('can get media for a model', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $this->service->upload($post, $file, 'gallery');

    $media = $this->service->getMediaForModel($post, 'gallery');

    expect($media)->toHaveCount(1);
});

it('can format file size', function () {
    expect($this->service->formatFileSize(1024))->toBe('1 KB');
    expect($this->service->formatFileSize(1024 * 1024))->toBe('1 MB');
    expect($this->service->formatFileSize(500))->toBe('500 B');
});

it('can determine file type category', function () {
    expect($this->service->getFileTypeCategory('image/jpeg'))->toBe('image');
    expect($this->service->getFileTypeCategory('video/mp4'))->toBe('video');
    expect($this->service->getFileTypeCategory('application/pdf'))->toBe('pdf');
    expect($this->service->getFileTypeCategory('application/msword'))->toBe('document');
    expect($this->service->getFileTypeCategory('application/vnd.ms-excel'))->toBe('spreadsheet');
    expect($this->service->getFileTypeCategory('application/octet-stream'))->toBe('file');
});

it('can validate file against configuration', function () {
    config()->set('media-manager.upload.max_file_size', 1024); // 1KB
    config()->set('media-manager.upload.allowed_mimes', ['image/jpeg']);

    $validFile = UploadedFile::fake()->create('test.jpg', 0.5, 'image/jpeg');
    $errors = $this->service->validateFile($validFile);
    expect($errors)->toBeEmpty();

    $largeFile = UploadedFile::fake()->create('large.jpg', 10, 'image/jpeg');
    $errors = $this->service->validateFile($largeFile);
    expect($errors)->not->toBeEmpty();
});
