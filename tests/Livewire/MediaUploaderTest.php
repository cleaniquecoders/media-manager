<?php

use CleaniqueCoders\MediaManager\Livewire\Uploader;
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

it('can render with model binding', function () {
    Livewire::test(Uploader::class, [
        'model' => $this->user,
        'collection' => 'avatar',
    ])
        ->assertStatus(200)
        ->assertSet('modelClass', User::class)
        ->assertSet('modelId', $this->user->id)
        ->assertSet('collection', 'avatar');
});

it('can set max files limit', function () {
    Livewire::test(Uploader::class, [
        'model' => $this->user,
        'maxFiles' => 5,
    ])
        ->assertSet('maxFiles', 5);
});

it('can set accepted types', function () {
    Livewire::test(Uploader::class, [
        'model' => $this->user,
        'acceptedTypes' => ['image/jpeg', 'image/png'],
    ])
        ->assertSet('acceptedTypes', ['image/jpeg', 'image/png']);
});

it('loads existing media when model is provided', function () {
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);
    $this->user->addMedia($file)->toMediaCollection('avatar');

    $component = Livewire::test(Uploader::class, [
        'model' => $this->user,
        'collection' => 'avatar',
    ]);

    expect($component->get('existingMedia'))->toHaveCount(1);
});

it('can remove existing media', function () {
    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('avatar');

    Livewire::test(Uploader::class, [
        'model' => $this->user,
        'collection' => 'avatar',
    ])
        ->call('removeExisting', $media->id)
        ->assertDispatched('media-removed');

    expect($media->fresh())->toBeNull();
});

it('does not save when there are no uploads', function () {
    Livewire::test(Uploader::class, [
        'model' => $this->user,
        'collection' => 'documents',
    ])
        ->call('save')
        ->assertNotDispatched('media-uploaded');

    expect($this->user->getMedia('documents'))->toHaveCount(0);
});

it('can update custom property values', function () {
    Livewire::test(Uploader::class, [
        'model' => $this->user,
    ])
        ->call('updatePropertyValue', 0, 'alt', 'Alternative text')
        ->assertSet('customProperties.0.alt', 'Alternative text');
});

it('can remove a pending upload from uploads array', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);

    $component = Livewire::test(Uploader::class, [
        'model' => $this->user,
    ])
        ->set('uploads', [$file1, $file2])
        ->call('removeUpload', 0);

    // After removal, only one file should remain
    expect($component->get('uploads'))->toHaveCount(1);
});
