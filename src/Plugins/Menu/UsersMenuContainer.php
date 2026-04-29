<?php

namespace Reno\Cms\Plugins\Menu;

class UsersMenuContainer extends AbstractTopMenuItem
{
    public function getId(): string
    {
        return 'users';
    }

    public function getLabel(): string
    {
        return 'users';
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
