<?php

namespace core\domain\strategy;

use core\domain\entity\Task;

class NoOpStrategy implements TaskSyncStrategyInterface
{
    public function execute(Task $sourceTask, Task $targetTask): void {}
}