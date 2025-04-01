<?php

namespace core\domain\strategy;

use core\domain\collection\TaskUpdatersCollection;
use core\domain\entity\Task;
use core\domain\TaskServiceInterface;

class TaskSyncStrategyFactory
{
    private TaskServiceInterface $taskRepositoryService;
    private array $apiServices;

    public function __construct(TaskServiceInterface $taskRepositoryService, TaskUpdatersCollection $apiServices)
    {
        $this->taskRepositoryService = $taskRepositoryService;
        $this->apiServices = $apiServices->getUpdaters();
    }

    public function createStrategy(Task $sourceTask, Task $targetTask): TaskSyncStrategyInterface
    {
        $apiService = $this->apiServices[$targetTask->getExternalSystem()];

        if ($targetTask->isNew()) {
            return new TaskCreateStrategy($apiService, $this->taskRepositoryService);
        }

        if ($sourceTask->isDeleted()) {
            return new TaskDeleteStrategy($apiService, $this->taskRepositoryService);
        }

        if ($targetTask->getName() !== $sourceTask->getName() || $targetTask->isCompleted() !== $sourceTask->isCompleted()) {
            return new TaskUpdateStrategy($apiService, $this->taskRepositoryService);
        }

        return new NoOpStrategy();
    }
}
