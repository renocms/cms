<?php

namespace Reno\Cms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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

        // Определяем путь для JS файлов
        $jsPath = str_replace('/lang', '/js/i18n', $langPath);

        // Создаем директорию для JS переводов, если её нет
        if (!File::exists($jsPath)) {
            File::makeDirectory($jsPath, 0755, true);
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
            $jsFile = $jsPath . '/' . $locale . '.js';

            if (!File::exists($phpFile)) {
                $this->warn("PHP file not found: {$phpFile}");
                continue;
            }

            $translations = require $phpFile;

            if (!is_array($translations)) {
                $this->warn("Invalid translations in {$phpFile}");
                continue;
            }

            $jsContent = $this->generateJsFile($translations);

            File::put($jsFile, $jsContent);

            $this->info("Generated: {$jsFile}");
        }

        $this->info('Translation files generated successfully!');
        return 0;
    }

    /**
     * Generate JavaScript file content from PHP translations array
     *
     * @param array $translations
     * @return string
     */
    private function generateJsFile(array $translations): string
    {
        $content = "export default {\n";
        $content .= $this->arrayToJs($translations, 1);
        $content .= "};\n";

        return $content;
    }

    /**
     * Convert PHP array to JavaScript object string
     *
     * @param array $array
     * @param int $indent
     * @return string
     */
    private function arrayToJs(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $lines = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $lines[] = $spaces . $this->escapeKey($key) . ': {';
                $lines[] = $this->arrayToJs($value, $indent + 1);
                $lines[] = $spaces . '},';
            } else {
                $lines[] = $spaces . $this->escapeKey($key) . ': ' . $this->escapeValue($value) . ',';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Escape JavaScript key
     *
     * @param string $key
     * @return string
     */
    private function escapeKey(string $key): string
    {
        // Если ключ содержит специальные символы, используем кавычки
        if (preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', $key)) {
            return $key;
        }

        return json_encode($key, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Escape JavaScript value
     *
     * @param mixed $value
     * @return string
     */
    private function escapeValue($value): string
    {
        if (is_string($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}

