<?php

namespace CleaniqueCoders\MediaManager\Tests\Fixtures;

use Illuminate\Filesystem\FilesystemAdapter;
use RuntimeException;

/**
 * A disk with no local path (like S3) that can be told to fail writes.
 */
class FaultyFilesystemAdapter extends FilesystemAdapter
{
    /** @var array<int, string> Path fragments whose writes throw */
    public array $failOn = [];

    public function path($path)
    {
        throw new RuntimeException('This disk has no local path.');
    }

    public function writeStream($path, $resource, array $options = [])
    {
        foreach ($this->failOn as $fragment) {
            if (str_contains($path, $fragment)) {
                throw new RuntimeException("Simulated write failure for [{$path}].");
            }
        }

        return parent::writeStream($path, $resource, $options);
    }
}
