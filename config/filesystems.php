<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('ASSET_URL', env('APP_URL')).'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Konfigurasi S3 untuk Cloudflare R2
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'), // Di R2, ini adalah Access Key ID dari token API R2
            'secret' => env('AWS_SECRET_ACCESS_KEY'), // Di R2, ini adalah Secret Access Key dari token API R2
            'region' => env('AWS_DEFAULT_REGION', 'auto'), // R2 menggunakan 'auto' atau spesifik jika diperlukan
            'bucket' => env('AWS_BUCKET'), // Nama bucket R2 Anda
            'endpoint' => env('AWS_ENDPOINT'), // Endpoint R2: https://<ACCOUNT_ID>.r2.cloudflarestorage.com
            'url' => env('AWS_URL'), // Opsional: URL publik bucket jika dikonfigurasi di R2
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false), // Biarkan false untuk R2
            'throw' => false,
            // 'visibility' => 'public', // Anda bisa atur visibility default di sini jika semua file public
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
