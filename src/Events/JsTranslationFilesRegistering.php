<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JsTranslationFilesRegistering
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<int, string>
     */
    private array $files = [];

    public function __construct(
        private readonly string $locale,
    )
    {
    }

    public function addFile(string $path): void
    {
        $this->files[] = $path;
    }

    /**
     * @return array<int, string>
     */
    public function getFiles(): array
    {
        return array_values(array_unique($this->files));
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
