<?php

namespace core\service;

use core\exception\AppException;
use core\App;
use core\SystemAssembler;

/**
 *
 */
class SystemService
{
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
     * @var SystemAssembler
     */
    private SystemAssembler $assembler;

    /**
     * @param array $targets
     * @param array $realizations
     * @return void
     * @throws AppException
     */
    public function __construct(array $targets, array $realizations)
    {
        $this->loadSourceSystems(array_keys($targets));
        $this->loadTargetSystems($targets);
        $this->assembler = new SystemAssembler($realizations);
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
    public function getSystemObject(string $systemName): object
    {
        return $this->loadSingleSystem($systemName);
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
            $this->systemInstances[$system] = $this->assembler->getComponent($system);
        }

        return $this->systemInstances[$system];
    }
}