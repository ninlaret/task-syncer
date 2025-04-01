<?php

namespace core\domain\strategy;

use core\domain\entity\Task;
use core\domain\TaskApiUpdateInterface;
use core\domain\TaskServiceInterface;

class TaskCreateStrategy implements TaskSyncStrategyInterface
{
    private TaskApiUpdateInterface $taskApiService;
    private TaskServiceInterface $taskService;

    public function __construct(TaskApiUpdateInterface $taskApiService, TaskServiceInterface $taskRepositoryService)
    {
        $this->taskApiService = $taskApiService;
        $this->taskService = $taskRepositoryService;
    }

    public function execute(Task $sourceTask, Task $targetTask): void
    {
        $targetId = $this->taskApiService->createTask($targetTask);
        $targetTask->setExternalId($targetId);

        $this->taskService->saveTarget($sourceTask, $targetTask);
    }
}
