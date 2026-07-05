<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configurations
    |--------------------------------------------------------------------------
    | Define the base URI prefix and middleware groups for the admin panel routes.
    */
    'route_prefix' => env('SJ_ADMIN_PREFIX', 'admin'),
    
    'middleware' => [
        'web',
        \safarjaisur\AdminPanel\Http\Middleware\AdminMiddleware::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    | SJ Admin Panel operates using default connections, with support for 
    | custom connections (e.g. host: localhost, db: alokwebsite2, user: root).
    */
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'alokwebsite2'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme and Branding Settings
    |--------------------------------------------------------------------------
    | Configures standard visual branding elements for the template.
    */
    'theme' => [
        'mode' => 'light', // light or dark
        'rtl' => false,
        'primary_color' => '#4e73df',
        'logo' => '/assets/sjadmin/img/logo.png',
        'favicon' => '/assets/sjadmin/img/favicon.ico',
        'title' => 'SJ Admin Panel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Manager Configuration
    |--------------------------------------------------------------------------
    | Storage disk and folder configurations for media uploads.
    */
    'media' => [
        'disk' => env('SJ_MEDIA_DISK', 'public'),
        'folder' => 'uploads',
        'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'application/json'],
        'max_size' => 5120, // in KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 15,
    ],
];