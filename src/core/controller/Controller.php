<?php

namespace core\controller;

/**
 *
 */
class Controller
{
    /**
     * @param string $action
     * @return string
     */
    public function run(string $action): mixed
    {
        $methodName = $action . 'Action';
        if (method_exists(get_class($this), $methodName)) {
            return $this->$methodName();
        } else {

            return 'No such action' . "\n";
        }
    }
}
