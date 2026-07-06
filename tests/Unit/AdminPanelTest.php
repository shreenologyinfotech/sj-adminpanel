<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Tests\Unit;

use Safarjaisur\AdminPanel\AdminPanel;
use Safarjaisur\AdminPanel\Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_it_registers_widgets(): void
    {
        $panel = new AdminPanel();
        $panel->registerWidget(\stdClass::class);

        $this->assertCount(1, $panel->widgets());
    }
}
