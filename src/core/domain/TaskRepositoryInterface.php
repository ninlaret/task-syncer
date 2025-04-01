<?php

namespace core\domain;

use core\domain\entity\Task;

interface TaskRepositoryInterface
{
    public function findBySystemAndId(string $externalSystem, string $externalId): ?Task;
    public function findBySystemAndSourceTask(string $externalSystem, int $id): ?Task;
    public function save(Task $task): void;
    public function delete(Task $task): void;
}