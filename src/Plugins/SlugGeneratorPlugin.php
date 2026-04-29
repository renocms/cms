<?php

namespace Reno\Cms\Plugins;

use Reno\Cms\Interfaces\JavascriptPluginInterface;

/**
 * Плагин для автоматической генерации slug на основе заголовка
 */
class SlugGeneratorPlugin implements JavascriptPluginInterface
{
    public function getName(): string
    {
        return 'slug-generator';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('plugins/resource-edit/slug-generator.js');
    }

    public function getConfig(): array
    {
        return [
            'fieldKey' => 'title', // Ключ поля заголовка в форме
        ];
    }
}
