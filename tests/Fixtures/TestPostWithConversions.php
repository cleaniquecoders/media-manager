<?php

namespace CleaniqueCoders\MediaManager\Tests\Fixtures;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestPostWithConversions extends TestPost
{
    public function registerMediaConversions(?Media $media = null): void
    {
        // Registered so file names resolve; never generated on upload in tests
        $this->addMediaConversion('thumb')->width(5)->performOnCollections('none');
    }
}
