<?php

namespace Reno\Cms\Plugins\Menu;

class SettingsMenuContainer extends AbstractTopMenuItem
{
    public function getId(): string
    {
        return 'settings';
    }

    public function getLabel(): string
    {
        return 'settings';
    }

    public function getPath(): ?string
    {
        return null;
    }

    public function getParentId(): ?string
    {
        return null;
    }

    public function getOrder(): int
    {
        return 20;
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
