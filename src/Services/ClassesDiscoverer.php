<?php

namespace Reno\Cms\Services;

use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class ClassesDiscoverer
{
    /**
     * @return array<class-string>
     */
    public function discover(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $appPath = realpath(app_path());
        if ($appPath === false) {
            return [];
        }

        $classes = [];
        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($directory)
            ->sortByName();

        foreach ($finder as $file) {
            $realPath = realpath($file->getPathname());
            if ($realPath === false) {
                continue;
            }

            $appPathPrefix = $appPath . DIRECTORY_SEPARATOR;
            if (!str_starts_with($realPath, $appPathPrefix)) {
                continue;
            }

            $relativePath = Str::beforeLast(
                substr($realPath, strlen($appPathPrefix)),
                '.php'
            );
            $className = 'App\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

            if (!class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            if (!$reflection->isInstantiable()) {
                continue;
            }

            $classes[] = $className;
        }

        return $classes;
    }
}
