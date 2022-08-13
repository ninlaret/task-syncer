<?php

namespace core\exception;

/**
 *
 */
class ApiException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}