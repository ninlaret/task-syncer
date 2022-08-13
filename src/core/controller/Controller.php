<?php

namespace core\controller;

/**
 *
 */
class Controller
{
    /**
     * @param $action
     * @return string
     */
    public function run($action)
    {
        $methodName = $action . 'Action';
        if (method_exists(get_class($this), $methodName)) {
            return $this->$methodName();
        } else {

            return 'No such action' . "\n";
        }
    }
}
