<?php

namespace core\domain\strategy;

use core\domain\entity\Task;
use core\domain\TaskApiUpdateInterface;
use core\domain\TaskServiceInterface;

class TaskDeleteStrategy implements TaskSyncStrategyInterface
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
        $this->taskApiService->deleteTask($targetTask);

        $this->taskService->deleteTarget($sourceTask, $targetTask);
    }
}