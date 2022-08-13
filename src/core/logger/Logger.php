<?php

namespace core\logger;

/**
 *
 */
abstract class Logger
{
    /**
     * @var $this
     */
    protected static self $instance;
    /**
     * @var bool
     */
    protected bool $showLog;

    /**
     * @return void
     */
    private function __constructor(): void
    {
    }

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
            self::$instance->showLog = App::$config['showLogs'];
        }

        return self::$instance;
    }

    /**
     * @param $message
     * @return void
     */
    abstract public function log($message): void;

    /**
     * @param $message
     * @return void
     */
    abstract public function error($message): void;
}