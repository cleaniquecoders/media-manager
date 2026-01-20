<?php

use CleaniqueCoders\MediaManager\Livewire\Browser;
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

it('can render the media browser component', function () {
    Livewire::test(Browser::class)
        ->assertStatus(200)
        ->assertViewIs('media-manager::livewire.media-browser');
});

it('can toggle between grid and list view', function () {
    Livewire::test(Browser::class)
        ->assertSet('view', 'grid')
        ->call('toggleView')
        ->assertSet('view', 'list')
        ->call('toggleView')
        ->assertSet('view', 'grid');
});

it('can set view directly', function () {
    Livewire::test(Browser::class)
        ->call('setView', 'list')
        ->assertSet('view', 'list')
        ->call('setView', 'grid')
        ->assertSet('view', 'grid');
});

it('can filter by search term', function () {
    Livewire::test(Browser::class)
        ->set('search', 'test document')
        ->assertSet('search', 'test document');
});

it('can filter by collection', function () {
    Livewire::test(Browser::class)
        ->set('collection', 'documents')
        ->assertSet('collection', 'documents');
});

it('can filter by type', function () {
    Livewire::test(Browser::class)
        ->set('type', 'image')
        ->assertSet('type', 'image');
});

it('can clear all filters', function () {
    Livewire::test(Browser::class)
        ->set('search', 'test')
        ->set('collection', 'documents')
        ->set('type', 'image')
        ->set('dateFrom', '2024-01-01')
        ->set('dateTo', '2024-12-31')
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('collection', '')
        ->assertSet('type', '')
        ->assertSet('dateFrom', '')
        ->assertSet('dateTo', '');
});

it('can select and deselect media', function () {
    // First upload some media
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Browser::class)
        ->call('toggleSelect', $media->id)
        ->assertSet('selected', [$media->id])
        ->call('toggleSelect', $media->id)
        ->assertSet('selected', []);
});

it('can open and close preview', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Browser::class)
        ->call('openPreview', $media->id)
        ->assertSet('previewMediaId', $media->id)
        ->call('closePreview')
        ->assertSet('previewMediaId', null);
});

it('can confirm and cancel delete', function () {
    Livewire::test(Browser::class)
        ->call('confirmDelete')
        ->assertSet('showDeleteConfirm', true)
        ->call('cancelDelete')
        ->assertSet('showDeleteConfirm', false);
});

it('can deselect all media', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);
    $media1 = $this->user->addMedia($file1)->toMediaCollection('default');
    $media2 = $this->user->addMedia($file2)->toMediaCollection('default');

    Livewire::test(Browser::class)
        ->call('toggleSelect', $media1->id)
        ->call('toggleSelect', $media2->id)
        ->assertSet('selected', [$media1->id, $media2->id])
        ->call('deselectAll')
        ->assertSet('selected', []);
});

it('can delete selected media', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Browser::class)
        ->call('toggleSelect', $media->id)
        ->call('deleteSelected')
        ->assertSet('selected', [])
        ->assertSet('showDeleteConfirm', false)
        ->assertDispatched('media-deleted');

    expect($media->fresh())->toBeNull();
});

it('can delete single media', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Browser::class)
        ->call('openPreview', $media->id)
        ->call('deleteSingle', $media->id)
        ->assertSet('previewMediaId', null)
        ->assertDispatched('media-deleted');

    expect($media->fresh())->toBeNull();
});

it('provides type options', function () {
    $component = Livewire::test(Browser::class);

    expect($component->get('typeOptions'))->toBeArray()
        ->toHaveKeys(['image', 'video', 'audio', 'pdf', 'document', 'spreadsheet']);
});
