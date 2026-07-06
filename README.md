# Safarjaisur AdminPanel

A modern, extensible admin panel package for **Laravel 12** / **PHP 8.2+**, built from scratch with SOLID
principles, the Repository pattern, and a Service layer — inspired by the feature set of Voyager, but with an
entirely original architecture, codebase, and UI (converted from the Axelit Bootstrap 5 admin template).

## Features

- Dashboard with pluggable widgets, stats and system status
- Isolated `sjadmin` auth guard (never collides with your app's own auth)
- Role-based access control (Roles, Permissions, Policies, Middleware, Gate integration)
- User management (CRUD, avatar, status, soft deletes, search)
- BREAD builder (Browse / Read / Edit / Add / Delete) generated from any DB table
- Database Manager (list tables, drop tables — migration/model generation hooks included)
- Menu Builder (DB-driven sidebar, nested items, icons, permissions, ordering)
- Media Manager (folders, upload, delete — local or S3 via the configured disk)
- Settings Manager (grouped key/value settings: site, SEO, mail, etc.)
- Dark mode toggle, RTL-ready layout, fully responsive
- Artisan generators: `make:safarjaisur-module`, `make:safarjaisur-widget`, `make:safarjaisur-bread`,
  `make:safarjaisur-menu`, `make:safarjaisur-setting`, `make:safarjaisur-permission`

## Installation

```bash
composer require safarjaisur/adminpanel
php artisan safarjaisuradmin:install
```

The installer will publish config, assets, views and language files, run migrations, seed a default
administrator, roles, permissions, the sidebar menu and default settings, and create the storage symlink.

### Default login

| Field    | Value                                        |
|----------|-----------------------------------------------|
| URL      | `/admin` (configurable via `sjadminpanel.route.prefix`) |
| Email    | `admin@example.com`                          |
| Password | `password`                                   |

**Change this password immediately in production.**

## Local development (path repository)

If you're developing this package alongside a Laravel app rather than pulling it from Packagist, add a path
repository to the host application's `composer.json`:

```json
{
    "repositories": [
        { "type": "path", "url": "packages/safarjaisur/adminpanel" }
    ],
    "require": {
        "safarjaisur/adminpanel": "@dev"
    }
}
```

Then run:

```bash
composer require safarjaisur/adminpanel:@dev
php artisan safarjaisuradmin:install
```

## Configuration

Publish and edit `config/sjadminpanel.php` to change the route prefix, middleware, theme, storage disk,
pagination, and default admin credentials:

```bash
php artisan vendor:publish --tag=sjadminpanel-config
```

## Extending

Register additional dashboard widgets, BREAD definitions, or menu items from your own `AppServiceProvider`:

```php
use Safarjaisur\AdminPanel\Facades\AdminPanel;

AdminPanel::registerWidget(\App\AdminWidgets\SalesWidget::class);
AdminPanel::registerBread('products', [...]);
```

## Requirements

- PHP ^8.2
- Laravel ^12.0

## Credits

UI converted from the Axelit HTML admin template into original Blade components, layouts and partials.
No Voyager code, views, controllers, or migrations were copied — only general feature concepts were used
as inspiration, re-implemented with a new architecture.

## License

MIT
