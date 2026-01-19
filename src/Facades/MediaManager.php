<?php

namespace CleaniqueCoders\MediaManager\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CleaniqueCoders\MediaManager\MediaManager
 */
class MediaManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CleaniqueCoders\MediaManager\MediaManager::class;
    }
}
