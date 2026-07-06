<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Tests\Feature;

use Safarjaisur\AdminPanel\Models\AdminUser;
use Safarjaisur\AdminPanel\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    public function test_install_command_seeds_default_admin(): void
    {
        $this->artisan('safarjaisuradmin:install')->assertSuccessful();

        $this->assertDatabaseHas('sjadmin_users', [
            'email' => config('sjadminpanel.default_admin.email'),
        ]);
    }
}
