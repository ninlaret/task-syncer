<?php

namespace core;

use core\exception\AppException;
use ReflectionClass;

/**
 *
 */
class SystemAssembler
{
    /**
     * @var array
     */
    private array $components = [];

    /**
     * @param array $conf
     * @throws AppException
     */
    public function __construct(array $conf)
    {
        $this->configure($conf);
    }

    /**
     * @param string $system
     * @return object
     * @throws AppException
     */
    public function getComponent(string $system): object
    {
        if (isset($this->components[$system])) {
            $instance = $this->components[$system]();
        } else {
            throw new AppException('Realization for the system ' . $system . ' does not exist. Please add it in the config/cli.php file');
        }

        return $instance;
    }

    /**
     * @param array $conf
     * @return void
     * @throws AppException
     */
    private function configure(array $conf): void
    {
        foreach ($conf as $key => $realization) {
            if (!class_exists($realization)) {
                throw new AppException('Wrong realization for the system ' . $key . ': class ' . $realization . ' does not exist');
            }

            $this->components[$key] = function () use ($realization) {
                $instance = new ReflectionClass ($realization);
                return $instance->newInstance();
            };
        }
    }
}