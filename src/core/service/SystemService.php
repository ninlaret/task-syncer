<?php

namespace core\service;

use core\exception\AppException;
use core\App;

/**
 *
 */
class SystemService
{
    /**
     * @var $this
     */
    private static self $instance;
    /**
     * @var array
     */
    private array $sourceSystems;
    /**
     * @var array
     */
    private array $targetSystems;
    /**
     * @var array
     */
    private array $systemInstances;
    /**
     * @var array
     */
    private array $systemNames;

    /**
     * @return void
     */
    private function __constructor(): void
    {
    }

    /**
     * @param array $config
     * @return static
     * @throws AppException
     */
    public static function init(array $config): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            self::$instance->loadSourceSystems(array_keys($config));
            self::$instance->loadTargetSystems($config);
            self::$instance->systemNames = $config;
        }

        return self::$instance;
    }

    /**
     * @return static
     * @throws \Exception
     */
    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            throw new \Exception('Please initialize SystemService first with init() method');
        }

        return self::$instance;
    }

    /**
     * @return array
     * @throws AppException
     */
    public function getSourceSystems(): array
    {
        $systems = array();

        foreach ($this->sourceSystems as $system) {
            $systems[] = $this->loadSingleSystem($system);
        }

        return $systems;
    }

    /**
     * @param string $system
     * @return array
     * @throws AppException
     */
    public function getTargetSystems(string $system): array
    {
        $systems = array();
        foreach ($this->targetSystems[$system] as $system) {
            $systems[] = $this->loadSingleSystem($system);
        }

        return $systems;
    }

    /**
     * @param string $systemName
     * @return object
     * @throws AppException
     */
    public function getSystem(string $systemName): object
    {
        return $this->loadSingleSystem($systemName);
    }

    /**
     * @param string $systemName
     * @return array
     */
    public function getTargetSystemNames(string $systemName): array
    {
        return $this->systemNames[$systemName] ?? array();
    }

    /**
     * @param $sourceConfig
     * @return void
     */
    private function loadSourceSystems($sourceConfig): void
    {
        $this->sourceSystems = $sourceConfig;
    }

    /**
     * @param array $targetConfig
     * @return void
     * @throws AppException
     */
    private function loadTargetSystems(array $targetConfig): void
    {
        foreach ($targetConfig as $targetSystem) {
            foreach ($targetSystem as $target) {
                if (isset($targetConfig[$target]) && in_array($targetSystem, $targetConfig[$target])) {
                     throw new AppException('Error: two systems can\'t be each other\'s targets: ' . $target . ' and ' . $targetSystem);
                }
            }
        }

        $this->targetSystems = $targetConfig;
    }

    /**
     * @param string $system
     * @return object
     * @throws AppException
     */
    private function loadSingleSystem(string $system): object
    {
        if (!isset(App::$config['apiRealisations'][$system])) {
            throw new AppException('Error: no realisation for system found in config:' . $system);
        }

        if (!isset($this->systemInstances[$system])) {
            $this->systemInstances[$system] = App::$assembler->getComponent($system);
        }

        return $this->systemInstances[$system];
    }
}