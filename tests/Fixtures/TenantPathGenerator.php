<?php

namespace CleaniqueCoders\MediaManager\Tests\Fixtures;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Lays each kind of file out in its own tree, so nothing nests inside the
 * original's directory the way DefaultPathGenerator's conversions do.
 */
class TenantPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return 'tenant-a/originals/'.$media->getKey().'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return 'tenant-a/conversions/'.$media->getKey().'/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return 'tenant-a/responsive/'.$media->getKey().'/';
    }
}
