<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel;

use Illuminate\Support\Collection;

/**
 * Central runtime registry for the admin panel.
 *
 * Acts as the single point of extension for host applications and
 * third-party packages: register dashboard widgets, menu items and
 * BREAD definitions without needing to publish or edit package files.
 */
class AdminPanel
{
    protected Collection $widgets;

    protected Collection $breads;

    protected Collection $extensionMenus;

    public function __construct()
    {
        $this->widgets = collect();
        $this->breads = collect();
        $this->extensionMenus = collect();
    }

    public function registerWidget(string $widgetClass): static
    {
        $this->widgets->push($widgetClass);

        return $this;
    }

    public function widgets(): Collection
    {
        return $this->widgets;
    }

    public function registerBread(string $slug, array $definition): static
    {
        $this->breads->put($slug, $definition);

        return $this;
    }

    public function breads(): Collection
    {
        return $this->breads;
    }

    public function registerMenuItem(array $item): static
    {
        $this->extensionMenus->push($item);

        return $this;
    }

    public function extensionMenuItems(): Collection
    {
        return $this->extensionMenus;
    }

    public function version(): string
    {
        return '1.0.0';
    }
}
