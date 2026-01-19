<?php

use CleaniqueCoders\MediaManager\Http\Controllers\MediaBrowserController;
use Illuminate\Support\Facades\Route;

Route::get('/', MediaBrowserController::class)->name('media-manager.index');
