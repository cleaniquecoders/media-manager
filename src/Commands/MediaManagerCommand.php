<?php

namespace CleaniqueCoders\MediaManager\Commands;

use Illuminate\Console\Command;

class MediaManagerCommand extends Command
{
    public $signature = 'media-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
