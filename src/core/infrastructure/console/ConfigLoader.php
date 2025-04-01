<?php

namespace core\infrastructure\console;

use DI\Container;
use DI\ContainerBuilder;

class ConfigLoader
{
    public static function buildContainer(?string $paramsPath = null, ?string $systemsPath = null): Container
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions(self::getLibraryConfigPath());

        $containerBuilder->addDefinitions(self::getUserConfigPath($paramsPath, 'params.php'));
        $containerBuilder->addDefinitions(self::getUserConfigPath($systemsPath, 'systems.php'));

        return $containerBuilder->build();
    }

    private static function getLibraryConfigPath(): string
    {
        return __DIR__ . '/../config/di.php';
    }

    private static function getUserConfigPath(?string $customPath, string $defaultFilename): string
    {
        if ($customPath !== null && file_exists($customPath)) {
            return $customPath;
        }

        $defaultPath = dirname(__DIR__, 4) . '/config/' . $defaultFilename;

        if (file_exists($defaultPath)) {
            return $defaultPath;
        }

        throw new \RuntimeException("Config dile not found: {$defaultFilename}");
    }
}