<?php

namespace core;

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
            error_log($message, 3, ROOT_PATH . '/debug.log');
            error_log("\n", 3, ROOT_PATH . '/debug.log');
        }
    }

    /**
     * @param $message
     * @return void
     */
    public function error($message): void
    {
        error_log($message, 3, ROOT_PATH . '/debug.log');
        error_log("\n", 3, ROOT_PATH . '/debug.log');
    }
}