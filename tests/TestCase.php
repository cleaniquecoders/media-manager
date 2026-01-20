<?php

namespace CleaniqueCoders\MediaManager\Tests;

use CleaniqueCoders\MediaManager\MediaManagerServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'CleaniqueCoders\\MediaManager\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            MediaManagerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // Set APP_KEY for encryption
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Media library config
        config()->set('media-library.disk_name', 'public');

        // View config - ensure we have a proper cache path
        config()->set('view.compiled', storage_path('framework/views'));

        // Run Spatie Media Library migrations
        $migration = include __DIR__.'/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub';
        $migration->up();

        // Create a test users table
        $app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create a test posts table
        $app['db']->connection()->getSchemaBuilder()->create('posts', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title');
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }
}
