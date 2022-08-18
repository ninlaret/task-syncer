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
     * @var TaskMapper
     */
    private TaskMapper $mapper;
    /**
     * @var SystemService
     */
    private SystemService $service;

    /**
     * @param array $targets
     * @param array $realizations
     * @return void
     */
    public function __construct(array $targets, array $realizations)
    {
        $this->mapper = new TaskMapper();
        $this->service = new SystemService($targets, $realizations);
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
        $tasks = array();

        foreach ($this->service->getSourceSystems() as $system) {
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
            $task = $system->create($parentTask->getName(), $parentTask->getCompleted());
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
        foreach ($this->service->getTargetSystems($task->getSystem()) as $system) {
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