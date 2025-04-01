<?php

namespace core\domain;

use core\application\ExternalTaskDTO;
use core\domain\entity\Task;

interface TaskServiceInterface
{
    public function getOrCreateSource(ExternalTaskDTO $dto): ?Task;
    public function getOrCreateTarget(int $id, string $targetSystem, ExternalTaskDTO $dto): ?Task;
    public function saveSource(Task $task): void;
    public function saveTarget(Task $sourceTask, Task $targetTask): void;
    public function deleteTarget(Task $sourceTask, Task $targetTask): void;
}