<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Widgets;

abstract class Widget
{
    abstract public function handle(): array;
}
