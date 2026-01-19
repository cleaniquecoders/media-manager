<?php

use CleaniqueCoders\MediaManager\Livewire\MediaPicker;
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

it('can render the media picker component', function () {
    Livewire::test(MediaPicker::class)
        ->assertStatus(200)
        ->assertViewIs('media-manager::livewire.media-picker');
});

it('can enable multiple selection', function () {
    Livewire::test(MediaPicker::class, [
        'multiple' => true,
    ])
        ->assertSet('multiple', true);
});

it('can disable multiple selection', function () {
    Livewire::test(MediaPicker::class, [
        'multiple' => false,
    ])
        ->assertSet('multiple', false);
});

it('can set custom label', function () {
    Livewire::test(MediaPicker::class, [
        'label' => 'Choose Files',
    ])
        ->assertSet('label', 'Choose Files');
});

it('can filter by collection', function () {
    Livewire::test(MediaPicker::class, [
        'collection' => 'images',
    ])
        ->assertSet('collection', 'images');
});

it('can filter by type', function () {
    Livewire::test(MediaPicker::class, [
        'type' => 'image',
    ])
        ->assertSet('type', 'image');
});

it('can open and close modal', function () {
    Livewire::test(MediaPicker::class)
        ->call('openModal')
        ->assertSet('showModal', true)
        ->call('closeModal')
        ->assertSet('showModal', false);
});

it('can toggle media selection in single mode', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(MediaPicker::class, [
        'multiple' => false,
    ])
        ->call('toggleSelect', $media->id)
        ->assertSet('tempSelected', [$media->id])
        ->call('toggleSelect', $media->id)
        ->assertSet('tempSelected', []);
});

it('can toggle media selection in multiple mode', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);
    $media1 = $this->user->addMedia($file1)->toMediaCollection('default');
    $media2 = $this->user->addMedia($file2)->toMediaCollection('default');

    Livewire::test(MediaPicker::class, [
        'multiple' => true,
    ])
        ->call('toggleSelect', $media1->id)
        ->call('toggleSelect', $media2->id)
        ->assertSet('tempSelected', [$media1->id, $media2->id]);
});

it('replaces selection in single mode', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);
    $media1 = $this->user->addMedia($file1)->toMediaCollection('default');
    $media2 = $this->user->addMedia($file2)->toMediaCollection('default');

    Livewire::test(MediaPicker::class, [
        'multiple' => false,
    ])
        ->call('toggleSelect', $media1->id)
        ->assertSet('tempSelected', [$media1->id])
        ->call('toggleSelect', $media2->id)
        ->assertSet('tempSelected', [$media2->id]);
});

it('can confirm selection', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(MediaPicker::class, [
        'multiple' => false,
    ])
        ->call('openModal')
        ->call('toggleSelect', $media->id)
        ->call('confirmSelection')
        ->assertSet('showModal', false)
        ->assertDispatched('media-selected');
});

it('can search media', function () {
    Livewire::test(MediaPicker::class)
        ->set('search', 'test document')
        ->assertSet('search', 'test document');
});

it('can remove selected media', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(MediaPicker::class)
        ->set('selected', [$media->id])
        ->call('removeSelected', $media->id)
        ->assertSet('selected', []);
});
