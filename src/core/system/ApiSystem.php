<?php

namespace core\system;

use core\exception\AppException;
use core\domain\Task;

/**
 *
 */
abstract class ApiSystem {
    /**
     * @param string $id
     * @param string $name
     * @param int $completed
     * @return Task
     * @throws AppException
     */
    public function makeTask(string $id, string $name, int $completed): Task
    {
        return new Task(0, $id, $name, $completed, static::getName());
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return array
     */
    abstract public function getAllTasks(): array;

    /**
     * @param string|int $id
     * @param bool $completed
     * @return void
     */
    abstract public function updateCompleted(string|int $id, bool $completed): void;

    /**
     * @param string|int $id
     * @param string $name
     * @return void
     */
    abstract public function updateName(string|int $id, string $name): void;

    /**
     * @param string $name
     * @param bool $completed
     * @return Task
     */
    abstract public function create(string $name, bool $completed = false): Task;
}