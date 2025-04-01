<?php

namespace core\domain\strategy;

use core\domain\entity\Task;
use core\domain\TaskApiUpdateInterface;
use core\domain\TaskServiceInterface;

class TaskUpdateStrategy implements TaskSyncStrategyInterface
{
    private TaskApiUpdateInterface $taskApiService;
    private TaskServiceInterface $taskService;

    public function __construct(TaskApiUpdateInterface $taskApiService, TaskServiceInterface $taskService)
    {
        $this->taskApiService = $taskApiService;
        $this->taskService = $taskService;
    }

    public function execute(Task $sourceTask, Task $targetTask): void
    {
        if ($sourceTask->getName() !== $targetTask->getName()) {
            $this->taskApiService->updateTaskName($targetTask->getExternalId(), $sourceTask->getName());
        }

        if ($sourceTask->isCompleted() !== $targetTask->isCompleted()) {
            $this->taskApiService->updateTaskStatus($targetTask->getExternalId(), $sourceTask->isCompleted());
        }

        $targetTask->setName($sourceTask->getName());
        $targetTask->setIsCompleted($sourceTask->isCompleted());

        $this->taskService->saveTarget($sourceTask, $targetTask);
    }
}