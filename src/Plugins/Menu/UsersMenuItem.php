<?php

namespace Reno\Cms\Plugins\Menu;

class UsersMenuItem extends AbstractTopMenuItem
{
    public function getId(): string
    {
        return 'users-sub';
    }

    public function getLabel(): string
    {
        return 'users';
    }

    public function getPath(): ?string
    {
        return 'users';
    }

    public function getParentId(): ?string
    {
        return 'users';
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
