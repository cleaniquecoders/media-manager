<?php

namespace CleaniqueCoders\MediaManager\Commands;

use Illuminate\Console\Command;

class MediaManagerCommand extends Command
{
    public $signature = 'media-manager:install {--force : Overwrite existing files}';

    public $description = 'Install the Media Manager package';

    public function handle(): int
    {
        $this->info('Installing Media Manager...');

        // Publish config
        $this->publishConfig();

        // Publish views (optional)
        if ($this->confirm('Would you like to publish the views for customization?', false)) {
            $this->publishViews();
        }

        // Run migrations
        if ($this->confirm('Would you like to run the Spatie Media Library migrations?', true)) {
            $this->call('vendor:publish', [
                '--provider' => 'Spatie\\MediaLibrary\\MediaLibraryServiceProvider',
                '--tag' => 'medialibrary-migrations',
            ]);
            $this->call('migrate');
        }

        $this->newLine();
        $this->info('Media Manager installed successfully!');
        $this->newLine();

        $this->components->info('Next steps:');
        $this->line('  1. Add the HasMediaManager trait to your models');
        $this->line('  2. Register media collections in your models');
        $this->line('  3. Visit /media-manager to browse media');

        $this->newLine();
        $this->components->info('Example model usage:');
        $this->line('  use CleaniqueCoders\MediaManager\Models\Traits\HasMediaManager;');
        $this->line('  use Spatie\MediaLibrary\HasMedia;');
        $this->newLine();
        $this->line('  class Post extends Model implements HasMedia');
        $this->line('  {');
        $this->line('      use HasMediaManager;');
        $this->line('  }');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $params = [
            '--provider' => 'CleaniqueCoders\\MediaManager\\MediaManagerServiceProvider',
            '--tag' => 'media-manager-config',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

    protected function publishViews(): void
    {
        $params = [
            '--provider' => 'CleaniqueCoders\\MediaManager\\MediaManagerServiceProvider',
            '--tag' => 'media-manager-views',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
