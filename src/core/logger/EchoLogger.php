<?php

namespace core\logger;

/**
 *
 */
class EchoLogger extends Logger
{
    /**
     * @param $message
     * @return void
     */
    public function log($message): void
    {
        if ($this->showLog) {
            echo $message;
            echo "\n";
        }
    }

    /**
     * @param $message
     * @return void
     */
    public function error($message): void
    {
        echo "\033[0;31mError: " . $message . "\033[0m";
        echo "\n";
    }
}