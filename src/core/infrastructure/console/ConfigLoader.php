<?php

namespace core\infrastructure\console;

use DI\Container;
use DI\ContainerBuilder;

class ConfigLoader
{
    public static function buildContainer(string $basePath, ?string $paramsPath = null, ?string $systemsPath = null): Container
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions(self::getLibraryConfigPath());

        $containerBuilder->addDefinitions([
            'base_path' => $basePath,
        ]);

        $containerBuilder->addDefinitions(self::getUserConfigPath($basePath, $paramsPath, 'params.php'));
        $containerBuilder->addDefinitions(self::getUserConfigPath($basePath, $systemsPath, 'systems.php'));

        return $containerBuilder->build();
    }

    private static function getLibraryConfigPath(): string
    {
        return __DIR__ . '/../config/di.php';
    }

    private static function getUserConfigPath(string $basePath, ?string $customPath, string $defaultFilename): string
    {
        if ($customPath !== null && file_exists($customPath)) {
            return $customPath;
        }

        $defaultPath = $basePath . '/config/' . $defaultFilename;

        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        throw new \RuntimeException("Config file not found: {$defaultFilename}");
    }
}