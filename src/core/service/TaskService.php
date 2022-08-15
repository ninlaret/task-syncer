<?php

namespace core\service;

use core\domain\Task;
use core\exception\AppException;
use core\mapper\TaskMapper;
use core\system\ApiSystem;

/**
 *
 */
class TaskService
{
    /**
     * @var self
     */
    private static self $instance;
    /**
     * @var TaskMapper
     */
    private TaskMapper $mapper;

    /**
     * @return void
     */
    private function __constructor(): void {}

    /**
     * @return static
     */
    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            self::$instance->mapper = new TaskMapper();
        }

        return self::$instance;
    }

    /**
     * @param $id
     * @param $system
     * @return Task|null
     * @throws AppException
     */
    public function find($id, $system): ? Task
    {
        return $this->mapper->find($id, $system);
    }

    /**
     * @return array
     * @throws AppException
     */
    public function getAllTasksFromSources(): array
    {
        $service = SystemService::getInstance();
        $tasks = array();

        foreach ($service->getSourceSystems() as $system) {
            $tasks = array_merge($tasks, $system->getAllTasks());
        }

        return $tasks;
    }

    /**
     * @param Task $parentTask
     * @param ApiSystem $system
     * @return void
     * @throws AppException
     */
    public function syncWithSystem(Task $parentTask, ApiSystem $system): void
    {
        $this->syncParentWithDatabase($parentTask);
        $task = $this->mapper->findConnected($parentTask->getParentId(), $system->getName());

        if (!$task) {
            $id = $system->sendTask($parentTask->getName(), $parentTask->getCompleted());
            $task = new Task(0, $id, $parentTask->getName(), $parentTask->getCompleted(), $system->getName());
            $this->mapper->insert($task, $parentTask->getId());
        } else {

            if ($task->getName() !== $parentTask->getName()) {
                $system->updateName($task->getExternalId(), $parentTask->getName());
                $task->setName($parentTask->getName());
            }

            if ($task->getCompleted() !== $parentTask->getCompleted()) {
                $system->updateCompleted($task->getExternalId(), $parentTask->getCompleted());
                $task->setCompleted($parentTask->getCompleted());
            }

            if ($task->isChanged()) $this->mapper->update($task);
        }
    }

    /**
     * @param Task $task
     * @return void
     * @throws AppException
     */
    public function syncWithTargets(Task $task): void
    {
        $service = SystemService::getInstance();

        foreach ($service->getTargetSystems($task->getSystem()) as $system) {
            $this->syncWithSystem($task, $system);
        }
    }

    /**
     * @param Task $task
     * @return void
     * @throws AppException
     */
    private function syncParentWithDatabase(Task $task): void
    {
        $dbTask = $this->mapper->find($task->getExternalId(), $task->getSystem());

        if ($dbTask) {
            $task->setParentId($dbTask->getParentId());

            if ($task->getName() !== $dbTask->getName() || $task->getCompleted() !== $dbTask->getCompleted()) {
                $this->mapper->update($task);
            }

            $task->setLocalId($dbTask->getId());
        } else {
            $this->mapper->insert($task);
        }
    }
}