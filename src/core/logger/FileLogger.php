<?php

namespace core\logger;

use core\App;

/**
 *
 */
class FileLogger extends Logger
{
    /**
     * @param $message
     * @return void
     */
    public function log($message): void
    {
        if ($this->showLog) {
            error_log($message, 3, App::$config['logPath']);
            error_log("\n", 3, App::$config['logPath']);
        }
    }

    /**
     * @param $message
     * @return void
     */
    public function error($message): void
    {
        error_log($message, 3, App::$config['logPath']);
        error_log("\n", 3, App::$config['logPath']);
    }
}