<?php

namespace CleaniqueCoders\MediaManager\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class MediaBrowserController extends Controller
{
    /**
     * Display the media browser page.
     */
    public function __invoke(): View
    {
        /** @var view-string $view */
        $view = 'media-manager::browser';

        return view($view);
    }
}
