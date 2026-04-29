<?php

namespace Reno\Cms\Interfaces\Layouts;

use Reno\Cms\Models\Resource;
use Reno\Cms\Interfaces\Forms\FormElementInterface;

interface ResourceLayoutInterface
{
    public function getName(): string;

    /**
     * @return string FQCN класса типа ресурса
     */
    public function getResourceType(): string;

    /**
     * @return array<FormElementInterface>
     */
    public function getSchema(): array;

    public function allowChildren(): bool;

    /**
     * @return array<string> Массив FQCN классов макетов для дочерних ресурсов
     */
    public function getChildrenLayouts(): array;

    public function getAttachedEntity(): ?string;

    public function getResourceCatalog(): ?string;

    public function getViewName(): ?string;

    public function getViewComposer(): ?string;
}
