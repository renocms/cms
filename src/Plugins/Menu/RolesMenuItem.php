<?php

namespace Reno\Cms\Plugins\Menu;

class RolesMenuItem extends AbstractTopMenuItem
{
    public function getId(): string
    {
        return 'roles-sub';
    }

    public function getLabel(): string
    {
        return 'roles';
    }

    public function getPath(): ?string
    {
        return 'roles';
    }

    public function getParentId(): ?string
    {
        return 'users';
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
