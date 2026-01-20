<?php

use CleaniqueCoders\MediaManager\Livewire\Picker;
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
    Livewire::test(Picker::class)
        ->assertStatus(200);
});

it('can enable multiple selection', function () {
    Livewire::test(Picker::class, [
        'multiple' => true,
    ])
        ->assertSet('multiple', true);
});

it('can disable multiple selection', function () {
    Livewire::test(Picker::class, [
        'multiple' => false,
    ])
        ->assertSet('multiple', false);
});

it('can search media', function () {
    Livewire::test(Picker::class)
        ->set('search', 'test document')
        ->assertSet('search', 'test document');
});

it('can open and close picker', function () {
    Livewire::test(Picker::class)
        ->call('openPicker')
        ->assertSet('isOpen', true)
        ->call('closePicker')
        ->assertSet('isOpen', false);
});

it('can toggle media selection in single mode', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Picker::class, [
        'multiple' => false,
    ])
        ->call('toggleSelect', $media->id)
        ->assertSet('selectedIds', [$media->id]);
});

it('can toggle media selection in multiple mode', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);
    $media1 = $this->user->addMedia($file1)->toMediaCollection('default');
    $media2 = $this->user->addMedia($file2)->toMediaCollection('default');

    Livewire::test(Picker::class, [
        'multiple' => true,
    ])
        ->call('toggleSelect', $media1->id)
        ->call('toggleSelect', $media2->id)
        ->assertSet('selectedIds', [$media1->id, $media2->id]);
});

it('replaces selection in single mode', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);
    $media1 = $this->user->addMedia($file1)->toMediaCollection('default');
    $media2 = $this->user->addMedia($file2)->toMediaCollection('default');

    Livewire::test(Picker::class, [
        'multiple' => false,
    ])
        ->call('toggleSelect', $media1->id)
        ->assertSet('selectedIds', [$media1->id])
        ->call('toggleSelect', $media2->id)
        ->assertSet('selectedIds', [$media2->id]);
});

it('can confirm selection and dispatch event', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Picker::class, [
        'multiple' => false,
    ])
        ->call('openPicker')
        ->call('toggleSelect', $media->id)
        ->call('confirm')
        ->assertSet('isOpen', false)
        ->assertDispatched('media-selected');
});

it('can remove selected media', function () {
    $file = UploadedFile::fake()->image('test.jpg', 100, 100);
    $media = $this->user->addMedia($file)->toMediaCollection('default');

    Livewire::test(Picker::class, [
        'selectedIds' => [$media->id],
    ])
        ->call('removeSelected', $media->id)
        ->assertSet('selectedIds', []);
});

it('can clear all selections', function () {
    $file1 = UploadedFile::fake()->image('test1.jpg', 100, 100);
    $file2 = UploadedFile::fake()->image('test2.jpg', 100, 100);
    $media1 = $this->user->addMedia($file1)->toMediaCollection('default');
    $media2 = $this->user->addMedia($file2)->toMediaCollection('default');

    Livewire::test(Picker::class, [
        'selectedIds' => [$media1->id, $media2->id],
    ])
        ->call('clearSelection')
        ->assertSet('selectedIds', []);
});

it('can filter by collection', function () {
    Livewire::test(Picker::class, [
        'collection' => 'images',
    ])
        ->assertSet('collection', 'images');
});

it('can filter by type', function () {
    Livewire::test(Picker::class, [
        'type' => 'image',
    ])
        ->assertSet('type', 'image');
});
