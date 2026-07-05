# SJ Admin Panel - Enterprise Laravel 12 Admin Suite

A modern, highly extensible BREAD and administration panel system designed for Laravel 12 and PHP 8.2.

## Installation

Install the package via Composer:

```bash
composer require safarjaisur/adminpanel
```

Execute the installation command to publish assets, configuration, views, run migrations, and seed default admin accounts:

```bash
php artisan sjadmin:install
```

## Credentials

Default credentials established during seeding:
- **Email**: `admin@example.com`
- **Password**: `password`

## Architecture Highlights
1. **SOLID Principles**: Direct segregation of routers, form requests, policies, services, and repositories.
2. **Repository Pattern**: Extracted base Eloquent wrappers to promote testability.
3. **BREAD Builder**: Dynamic database-to-UI scaffolding modeled visually.
4. **Theme customizers**: Full responsive blade structures supporting both Light & Dark modes, including RTL views.
