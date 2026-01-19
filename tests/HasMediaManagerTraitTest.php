<?php

use CleaniqueCoders\MediaManager\Tests\Fixtures\TestPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('can upload media using trait method', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    $media = $post->uploadMedia($file, 'gallery');

    expect($media)->not->toBeNull();
    expect($media->collection_name)->toBe('gallery');
});

it('can upload multiple media using trait method', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $files = [
        UploadedFile::fake()->image('test1.jpg', 100, 100),
        UploadedFile::fake()->image('test2.jpg', 100, 100),
    ];

    $uploaded = $post->uploadMediaMultiple($files, 'gallery');

    expect($uploaded)->toHaveCount(2);
});

it('can get ordered media', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);

    $post->uploadMedia($file1, 'gallery');
    $post->uploadMedia($file2, 'gallery');

    $ordered = $post->getMediaOrdered('gallery');

    expect($ordered)->toHaveCount(2);
});

it('can check if model has media in collection', function () {
    $post = TestPost::create(['title' => 'Test Post']);

    expect($post->hasMediaInCollection('gallery'))->toBeFalse();

    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $post->uploadMedia($file, 'gallery');

    // Refresh to get updated media relationship
    $post->refresh();

    expect($post->hasMediaInCollection('gallery'))->toBeTrue();
});

it('can get media count', function () {
    $post = TestPost::create(['title' => 'Test Post']);

    expect($post->getMediaCount('gallery'))->toBe(0);

    $files = [
        UploadedFile::fake()->image('test1.jpg', 100, 100),
        UploadedFile::fake()->image('test2.jpg', 100, 100),
    ];
    $post->uploadMediaMultiple($files, 'gallery');

    // Refresh to get updated media relationship
    $post->refresh();

    expect($post->getMediaCount('gallery'))->toBe(2);
});

it('can clear media in collection', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $files = [
        UploadedFile::fake()->image('test1.jpg', 100, 100),
        UploadedFile::fake()->image('test2.jpg', 100, 100),
    ];
    $post->uploadMediaMultiple($files, 'gallery');

    expect($post->getMediaCount('gallery'))->toBe(2);

    $post->clearMediaInCollection('gallery');

    expect($post->getMediaCount('gallery'))->toBe(0);
});

it('can get collections with media', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->uploadMedia(UploadedFile::fake()->image('test.jpg'), 'gallery');
    $post->uploadMedia(UploadedFile::fake()->image('featured.jpg'), 'featured');

    $collections = $post->getCollectionsWithMedia();

    expect($collections)->toContain('gallery');
    expect($collections)->toContain('featured');
});

it('can get media grouped by collection', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->uploadMedia(UploadedFile::fake()->image('test.jpg'), 'gallery');
    $post->uploadMedia(UploadedFile::fake()->image('featured.jpg'), 'featured');

    $grouped = $post->getMediaGroupedByCollection();

    expect($grouped->has('gallery'))->toBeTrue();
    expect($grouped->has('featured'))->toBeTrue();
});

it('can get media url', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->uploadMedia(UploadedFile::fake()->image('test.jpg'), 'gallery');

    $url = $post->getMediaUrl('gallery');

    expect($url)->not->toBeNull();
    expect($url)->toBeString();
});

it('returns null for empty collection url', function () {
    $post = TestPost::create(['title' => 'Test Post']);

    $url = $post->getMediaUrl('gallery');

    expect($url)->toBeNull();
});

it('can get all media urls', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->uploadMediaMultiple([
        UploadedFile::fake()->image('test1.jpg'),
        UploadedFile::fake()->image('test2.jpg'),
    ], 'gallery');

    $urls = $post->getMediaUrls('gallery');

    expect($urls)->toHaveCount(2);
});
