<?php

namespace core\logger;

use core\App;

/**
 *
 */
abstract class Logger
{
    /**
     * @var bool
     */
    protected bool $showLog;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->showLog = App::$config['showLogs'];
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