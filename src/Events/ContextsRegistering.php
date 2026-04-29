<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\Contexts\ContextInterface;

/**
 * Событие для регистрации контекстов CMS.
 *
 * Запускается из ContextsRepository при получении всех контекстов.
 * Подключаемые модули могут слушать это событие и регистрировать свои контексты.
 *
 * Пример в ServiceProvider приложения:
 *
 * ```php
 * use Illuminate\Support\Facades\Event;
 * use Reno\Cms\Events\ContextsRegistering;
 * use App\Reno\Contexts\CustomContext;
 *
 * public function boot(): void
 * {
 *     Event::listen(ContextsRegistering::class, function (ContextsRegistering $event) {
 *         $event->addContext(new CustomContext());
 *     });
 * }
 * ```
 */
class ContextsRegistering
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var array<ContextInterface>
     */
    protected array $contexts = [];

    public function addContext(ContextInterface $context): void
    {
        $this->contexts[] = $context;
    }

    /**
     * @return array<ContextInterface>
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }
}
