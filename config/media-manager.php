<?php

// config for CleaniqueCoders/MediaManager
return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routes for the media manager browser interface.
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'media-manager',
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure upload restrictions and behavior.
    |
    */
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'video/mp4',
            'video/webm',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'chunk_size' => 1024 * 1024, // 1MB chunks
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Settings
    |--------------------------------------------------------------------------
    |
    | Configure the media browser appearance and behavior.
    |
    */
    'browser' => [
        'default_view' => 'grid', // grid|list
        'items_per_page' => 24,
        'columns' => 4, // grid columns
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Configure authorization for the media manager.
    | Set 'gate' to null to allow all authenticated users.
    |
    */
    'authorization' => [
        'gate' => null, // e.g., 'manage-media'
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary Upload Disk
    |--------------------------------------------------------------------------
    |
    | The disk to use for temporary uploads before attaching to a model.
    |
    */
    'temporary_disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Temporary Upload Expiration
    |--------------------------------------------------------------------------
    |
    | How long temporary uploads should be kept (in hours) before cleanup.
    |
    */
    'temporary_upload_expiration' => 24,
];
