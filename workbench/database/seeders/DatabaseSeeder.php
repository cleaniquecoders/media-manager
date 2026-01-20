<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        Post::firstOrCreate(
            ['title' => 'Sample Post'],
            [
                'user_id' => $user->id,
                'content' => 'This is sample content for testing the media manager.',
            ]
        );

        Post::firstOrCreate(
            ['title' => 'Another Post'],
            [
                'user_id' => $user->id,
                'content' => 'Another sample post for testing multiple models.',
            ]
        );
    }
}
