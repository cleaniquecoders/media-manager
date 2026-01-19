<?php

use CleaniqueCoders\MediaManager\Support\MediaFilter;
use CleaniqueCoders\MediaManager\Tests\Fixtures\TestPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->filter = new MediaFilter;
});

it('can filter media by search term', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->addMedia(UploadedFile::fake()->image('unique-name.jpg'))
        ->usingName('Unique Photo')
        ->toMediaCollection('gallery');
    $post->addMedia(UploadedFile::fake()->image('other.jpg'))
        ->usingName('Other Photo')
        ->toMediaCollection('gallery');

    $query = Media::query();
    $this->filter->apply($query, ['search' => 'unique']);
    $results = $query->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Unique Photo');
});

it('can filter media by collection', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->addMedia(UploadedFile::fake()->image('gallery.jpg'))->toMediaCollection('gallery');
    $post->addMedia(UploadedFile::fake()->image('featured.jpg'))->toMediaCollection('featured');

    $query = Media::query();
    $this->filter->apply($query, ['collection' => 'gallery']);
    $results = $query->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->collection_name)->toBe('gallery');
});

it('can filter media by type', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->addMedia(UploadedFile::fake()->image('photo.jpg'))->toMediaCollection('gallery');
    $post->addMedia(UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))->toMediaCollection('documents');

    $query = Media::query();
    $this->filter->apply($query, ['type' => 'image']);
    $results = $query->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->mime_type)->toBe('image/jpeg');
});

it('can filter media by date range', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->addMedia(UploadedFile::fake()->image('today.jpg'))->toMediaCollection('gallery');

    $query = Media::query();
    $this->filter->apply($query, [
        'date_from' => now()->subDay()->format('Y-m-d'),
        'date_to' => now()->addDay()->format('Y-m-d'),
    ]);
    $results = $query->get();

    expect($results)->toHaveCount(1);
});

it('can apply multiple filters', function () {
    $post = TestPost::create(['title' => 'Test Post']);
    $post->addMedia(UploadedFile::fake()->image('matching.jpg'))
        ->usingName('Matching')
        ->toMediaCollection('gallery');
    $post->addMedia(UploadedFile::fake()->image('other.jpg'))
        ->usingName('Other')
        ->toMediaCollection('gallery');
    $post->addMedia(UploadedFile::fake()->image('featured.jpg'))
        ->usingName('Matching Featured')
        ->toMediaCollection('featured');

    $query = Media::query();
    $this->filter->apply($query, [
        'search' => 'Matching',
        'collection' => 'gallery',
    ]);
    $results = $query->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Matching');
    expect($results->first()->collection_name)->toBe('gallery');
});
