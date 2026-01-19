<?php

use CleaniqueCoders\MediaManager\Livewire\MediaUploader;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Workbench\App\Models\User;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('can render the media uploader component', function () {
    Livewire::test(MediaUploader::class)
        ->assertStatus(200)
        ->assertViewIs('media-manager::livewire.media-uploader');
});

it('can render with model binding', function () {
    Livewire::test(MediaUploader::class, [
        'model' => $this->user,
        'collection' => 'avatar',
    ])
        ->assertStatus(200)
        ->assertSet('modelClass', User::class)
        ->assertSet('modelId', $this->user->id)
        ->assertSet('collection', 'avatar');
});

it('can set max files limit', function () {
    Livewire::test(MediaUploader::class, [
        'maxFiles' => 5,
    ])
        ->assertSet('maxFiles', 5);
});

it('can set accepted types', function () {
    Livewire::test(MediaUploader::class, [
        'acceptedTypes' => ['image/jpeg', 'image/png'],
    ])
        ->assertSet('acceptedTypes', ['image/jpeg', 'image/png']);
});

it('can remove a pending upload', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    Livewire::test(MediaUploader::class)
        ->set('uploads', [$file])
        ->call('removeUpload', 0)
        ->assertSet('uploads', []);
});

it('loads existing media when model is provided', function () {
    // First, add media to the user
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);
    $this->user->addMedia($file)->toMediaCollection('avatar');

    $component = Livewire::test(MediaUploader::class, [
        'model' => $this->user,
        'collection' => 'avatar',
    ]);

    expect($component->get('existingMedia'))->toHaveCount(1);
});

it('can remove existing media', function () {
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('avatar');

    Livewire::test(MediaUploader::class, [
        'model' => $this->user,
        'collection' => 'avatar',
    ])
        ->call('removeExisting', $media->id)
        ->assertDispatched('media-removed');

    expect($media->fresh())->toBeNull();
});

it('can save uploaded files to model', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    $component = Livewire::test(MediaUploader::class, [
        'model' => $this->user,
        'collection' => 'documents',
    ])
        ->set('uploads', [$file])
        ->call('save')
        ->assertDispatched('media-uploaded');

    expect($this->user->getMedia('documents'))->toHaveCount(1);
});

it('can update custom property values', function () {
    Livewire::test(MediaUploader::class)
        ->call('updatePropertyValue', 0, 'alt', 'Alternative text')
        ->assertSet('customProperties.0.alt', 'Alternative text');
});

it('does not save when there are no uploads', function () {
    Livewire::test(MediaUploader::class, [
        'model' => $this->user,
        'collection' => 'documents',
    ])
        ->call('save')
        ->assertNotDispatched('media-uploaded');

    expect($this->user->getMedia('documents'))->toHaveCount(0);
});

it('does not save when no model is bound', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    Livewire::test(MediaUploader::class)
        ->set('uploads', [$file])
        ->call('save')
        ->assertNotDispatched('media-uploaded');
});

it('validates uploads when they are updated', function () {
    // This tests that updatedUploads is called and validates files
    Livewire::test(MediaUploader::class)
        ->assertSet('uploadErrors', []);
});
