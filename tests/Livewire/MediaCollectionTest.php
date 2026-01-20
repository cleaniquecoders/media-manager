<?php

use CleaniqueCoders\MediaManager\Livewire\Collection;
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
    Livewire::test(Collection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->assertStatus(200)
        ->assertSet('collection', 'gallery');
});

it('can enable sortable mode', function () {
    Livewire::test(Collection::class, [
        'model' => $this->user,
        'sortable' => true,
    ])
        ->assertSet('sortable', true);
});

it('can disable sortable mode', function () {
    Livewire::test(Collection::class, [
        'model' => $this->user,
        'sortable' => false,
    ])
        ->assertSet('sortable', false);
});

it('can set max files limit', function () {
    Livewire::test(Collection::class, [
        'model' => $this->user,
        'maxFiles' => 10,
    ])
        ->assertSet('maxFiles', 10);
});

it('can toggle upload zone', function () {
    Livewire::test(Collection::class, [
        'model' => $this->user,
    ])
        ->assertSet('showUploadZone', false)
        ->call('toggleUploadZone')
        ->assertSet('showUploadZone', true);
});

it('loads existing media when model is provided', function () {
    $file1 = UploadedFile::fake()->image('image1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('image2.jpg', 100, 100);
    $this->user->addMedia($file1)->toMediaCollection('gallery');
    $this->user->addMedia($file2)->toMediaCollection('gallery');

    $component = Livewire::test(Collection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ]);

    expect($component->get('media'))->toHaveCount(2);
});

it('can remove existing media', function () {
    $file = UploadedFile::fake()->image('image.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('gallery');

    Livewire::test(Collection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->call('removeMedia', $media->id)
        ->assertDispatched('media-removed');

    expect($media->fresh())->toBeNull();
});

it('can toggle edit mode for a media item', function () {
    $file = UploadedFile::fake()->image('image.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('gallery');

    Livewire::test(Collection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->call('editMedia', $media->id)
        ->assertSet('editingMediaId', $media->id)
        ->call('cancelEdit')
        ->assertSet('editingMediaId', null);
});

it('does not upload when there are no files', function () {
    Livewire::test(Collection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->call('uploadFiles')
        ->assertNotDispatched('media-uploaded');
});

it('can remove a pending upload from array', function () {
    Livewire::test(Collection::class, [
        'model' => $this->user,
    ])
        ->set('uploads', ['file1', 'file2'])
        ->call('removeUpload', 0)
        ->assertSet('uploads', ['file2']);
});
