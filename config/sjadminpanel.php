<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'route' => [
        'prefix' => env('SJADMIN_ROUTE_PREFIX', 'admin'),
        'middleware' => ['web', 'sjadmin.auth'],
        'domain' => env('SJADMIN_ROUTE_DOMAIN', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */
    'auth' => [
        'guard' => 'sjadmin',
        'provider' => 'sjadmin_users',
        'model' => \Safarjaisur\AdminPanel\Models\AdminUser::class,
        'redirect_after_login' => 'sjadmin.dashboard',
        'redirect_after_logout' => 'sjadmin.login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Admin (used by the installer / seeder)
    |--------------------------------------------------------------------------
    */
    'default_admin' => [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'password',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */
    'theme' => [
        'name' => env('SJADMIN_THEME', 'axelit'),
        'dark_mode' => true,
        'rtl' => false,
        'logo' => 'vendor/sjadminpanel/images/logo/1.png',
        'favicon' => 'vendor/sjadminpanel/images/logo/favicon.png',
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage / Media
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => env('SJADMIN_DISK', 'public'),
        'media_path' => 'media',
    ],

    'media' => [
        'thumbnails' => [
            'small' => [150, 150],
            'medium' => [400, 400],
            'large' => [1024, 1024],
        ],
        'driver' => env('SJADMIN_IMAGE_DRIVER', 'gd'), // 'gd' or 'imagick'
        'max_upload_size' => 10240, // KB
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'zip', 'doc', 'docx'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backups / Logs
    |--------------------------------------------------------------------------
    */
    'backups' => [
        'disk' => env('SJADMIN_BACKUP_DISK', 'local'),
        'path' => env('SJADMIN_BACKUP_PATH', 'sjadmin-backups'),
    ],

    'logs' => [
        'max_lines' => env('SJADMIN_LOG_MAX_LINES', 500),
    ],

    'files' => [
        'disk' => env('SJADMIN_FILE_DISK', 'local'),
        'root' => env('SJADMIN_FILE_ROOT', 'file-manager'),
        'max_upload_size' => env('SJADMIN_FILE_MAX_UPLOAD_SIZE', 20480),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    */
    'language' => [
        'default' => 'en',
        'available' => ['en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'widgets' => [
            \Safarjaisur\AdminPanel\Widgets\UsersCountWidget::class,
            \Safarjaisur\AdminPanel\Widgets\RecentUsersWidget::class,
            \Safarjaisur\AdminPanel\Widgets\SystemStatusWidget::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | BREAD
    |--------------------------------------------------------------------------
    */
    'bread' => [
        'table' => 'sjadmin_breads',
    ],
];
