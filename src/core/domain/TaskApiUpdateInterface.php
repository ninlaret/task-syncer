<?php

namespace core\domain;

use core\domain\entity\Task;

interface TaskApiUpdateInterface
{
    public function updateTaskStatus(string $taskId, bool $isCompleted): void;
    public function updateTaskName(string $taskId, string $name): void;
    public function createTask(Task $task): string;
    public function deleteTask(Task $task): void;
}