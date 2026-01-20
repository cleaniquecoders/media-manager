<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/demo', function () {
    $user = User::first();

    return view('demo', compact('user'));
})->name('demo');

Route::get('/uploader', function () {
    $user = User::first();

    return view('uploader-demo', compact('user'));
})->name('uploader');

Route::get('/collection', function () {
    $user = User::first();

    return view('collection-demo', compact('user'));
})->name('collection');

Route::view('/picker', 'picker-demo')->name('picker');

Route::get('/model-demo', function () {
    $user = User::first();
    $post = Post::first() ?? Post::create([
        'title' => 'Sample Post',
        'content' => 'This is sample content.',
        'user_id' => $user->id,
    ]);

    return view('model-demo', compact('user', 'post'));
})->name('model-demo');
