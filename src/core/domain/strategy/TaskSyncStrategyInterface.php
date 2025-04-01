<?php

namespace core\domain\strategy;

use core\domain\entity\Task;

interface TaskSyncStrategyInterface
{
    public function execute(Task $sourceTask, Task $targetTask): void;
}