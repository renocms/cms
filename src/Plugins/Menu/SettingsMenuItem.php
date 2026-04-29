<?php

namespace Reno\Cms\Plugins\Menu;

class SettingsMenuItem extends AbstractTopMenuItem
{
    public function getId(): string
    {
        return 'settings-sub';
    }

    public function getLabel(): string
    {
        return 'site_settings';
    }

    public function getPath(): ?string
    {
        return 'settings';
    }

    public function getParentId(): ?string
    {
        return 'settings';
    }

    public function getOrder(): int
    {
        return 10;
    }

    public function getIcon(): ?string
    {
        return null;
    }

    public function isVisible(): bool
    {
        return true;
    }
}
