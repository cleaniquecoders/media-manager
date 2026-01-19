<?php

namespace CleaniqueCoders\MediaManager\Tests\Fixtures;

use CleaniqueCoders\MediaManager\Concerns\HasMediaManager;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

class TestPost extends Model implements HasMedia
{
    use HasMediaManager;

    protected $table = 'posts';

    protected $fillable = ['title'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
        $this->addMediaCollection('gallery');
        $this->addMediaCollection('documents');
    }
}
