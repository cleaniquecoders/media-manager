<?php

use CleaniqueCoders\MediaManager\Livewire\MediaCollection;
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

it('can render the media collection component', function () {
    Livewire::test(MediaCollection::class)
        ->assertStatus(200)
        ->assertViewIs('media-manager::livewire.media-collection');
});

it('can render with model binding', function () {
    Livewire::test(MediaCollection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->assertStatus(200)
        ->assertSet('collection', 'gallery');
});

it('can enable sortable mode', function () {
    Livewire::test(MediaCollection::class, [
        'sortable' => true,
    ])
        ->assertSet('sortable', true);
});

it('can disable sortable mode', function () {
    Livewire::test(MediaCollection::class, [
        'sortable' => false,
    ])
        ->assertSet('sortable', false);
});

it('can set max files limit', function () {
    Livewire::test(MediaCollection::class, [
        'maxFiles' => 10,
    ])
        ->assertSet('maxFiles', 10);
});

it('can show upload zone', function () {
    Livewire::test(MediaCollection::class, [
        'showUploadZone' => true,
    ])
        ->assertSet('showUploadZone', true);
});

it('can hide upload zone', function () {
    Livewire::test(MediaCollection::class, [
        'showUploadZone' => false,
    ])
        ->assertSet('showUploadZone', false);
});

it('loads existing media when model is provided', function () {
    $file1 = UploadedFile::fake()->image('image1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('image2.jpg', 100, 100);
    $this->user->addMedia($file1)->toMediaCollection('gallery');
    $this->user->addMedia($file2)->toMediaCollection('gallery');

    $component = Livewire::test(MediaCollection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ]);

    expect($component->get('existingMedia'))->toHaveCount(2);
});

it('can remove a pending upload', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    Livewire::test(MediaCollection::class)
        ->set('uploads', [$file])
        ->call('removeUpload', 0)
        ->assertSet('uploads', []);
});

it('can remove existing media', function () {
    $file = UploadedFile::fake()->image('image.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('gallery');

    Livewire::test(MediaCollection::class, [
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

    Livewire::test(MediaCollection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->call('toggleEdit', $media->id)
        ->assertSet('editingMediaId', $media->id)
        ->call('toggleEdit', $media->id)
        ->assertSet('editingMediaId', null);
});

it('can save uploaded files', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);

    Livewire::test(MediaCollection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->set('uploads', [$file])
        ->call('save')
        ->assertDispatched('media-uploaded');

    expect($this->user->getMedia('gallery'))->toHaveCount(1);
});

it('does not save when there are no uploads', function () {
    Livewire::test(MediaCollection::class, [
        'model' => $this->user,
        'collection' => 'gallery',
    ])
        ->call('save')
        ->assertNotDispatched('media-uploaded');
});
