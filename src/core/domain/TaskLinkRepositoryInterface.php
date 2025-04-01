<?php

namespace core\domain;

use core\domain\entity\Task;
use core\domain\entity\TaskLink;

interface TaskLinkRepositoryInterface
{
    public function createTaskLink(TaskLink $taskLink): void;
    public function updateTaskLink(TaskLink $taskLink): void;
    public function findBySourceAndTarget(Task $sourceTask, Task $targetTask): ?TaskLink;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
}