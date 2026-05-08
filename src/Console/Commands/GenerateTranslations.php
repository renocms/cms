<?php

namespace Reno\Cms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Reno\Cms\Events\JsTranslationFilesRegistering;

class GenerateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:generate-js-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JavaScript translation files from PHP language files';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // Определяем путь к ресурсам пакета
        // В vendor путь: vendor/reno/cms/resources/lang
        // В разработке: reno-cms/resources/lang
        $possiblePaths = [
            base_path('vendor/reno/cms/resources/lang'),
            dirname(__DIR__, 3) . '/resources/lang',
            __DIR__ . '/../../resources/lang',
        ];

        $langPath = null;
        foreach ($possiblePaths as $path) {
            if (File::exists($path)) {
                $langPath = $path;
                break;
            }
        }

        if (!$langPath) {
            $this->error('Language files directory not found. Tried: ' . implode(', ', $possiblePaths));
            return 1;
        }

        $runtimeJsPath = $this->getRuntimeTranslationsPath($langPath);

        if (!File::exists($runtimeJsPath)) {
            File::makeDirectory($runtimeJsPath, 0755, true);
        }

        // Получаем список языков из PHP файлов
        $locales = [];
        if (File::exists($langPath)) {
            $directories = File::directories($langPath);
            foreach ($directories as $directory) {
                $locale = basename($directory);
                $cmsFile = $directory . '/cms.php';
                if (File::exists($cmsFile)) {
                    $locales[] = $locale;
                }
            }
        }

        if (empty($locales)) {
            $this->error('No language files found in ' . $langPath);
            return 1;
        }

        $this->info('Found locales: ' . implode(', ', $locales));

        // Генерируем JS файлы для каждого языка
        foreach ($locales as $locale) {
            $phpFile = $langPath . '/' . $locale . '/cms.php';
            $runtimeJsFile = $runtimeJsPath . '/' . $locale . '.js';

            if (!File::exists($phpFile)) {
                $this->warn("PHP file not found: {$phpFile}");
                continue;
            }

            $translations = require $phpFile;

            if (!is_array($translations)) {
                $this->warn("Invalid translations in {$phpFile}");
                continue;
            }

            $translations = $this->mergePackageTranslations($translations, $locale);

            $runtimeJsContent = $this->generateRuntimeJsFile($translations);

            File::put($runtimeJsFile, $runtimeJsContent);

            $this->info("Generated: {$runtimeJsFile}");
        }

        $this->info('Translation files generated successfully!');
        return 0;
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, mixed>
     */
    private function mergePackageTranslations(array $translations, string $locale): array
    {
        foreach ($this->getAdditionalTranslationFiles($locale) as $additionalTranslationFile) {
            $additionalTranslations = require $additionalTranslationFile;
            if (!is_array($additionalTranslations)) {
                $this->warn("Invalid translations in {$additionalTranslationFile}");
                continue;
            }

            $translations = array_merge($translations, $additionalTranslations);
        }

        return $translations;
    }

    /**
     * @return array<int, string>
     */
    private function getAdditionalTranslationFiles(string $locale): array
    {
        $event = new JsTranslationFilesRegistering($locale);
        Event::dispatch($event);

        $result = [];
        foreach ($event->getFiles() as $file) {
            if (File::exists($file)) {
                $result[] = $file;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @param array<string, mixed> $translations
     */
    private function generateRuntimeJsFile(array $translations): string
    {
        $json = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{}';
        }

        return "window.CMS_TRANSLATIONS = {$json};\n";
    }

    private function getRuntimeTranslationsPath(string $langPath): string
    {
        return str_replace('/lang', '/js/i18n-runtime', $langPath);
    }
}
