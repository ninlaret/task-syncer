<?php

namespace core\infrastructure\service;

use core\application\ExternalTaskDTO;
use core\domain\entity\Task;
use core\domain\entity\TaskLink;
use core\domain\TaskLinkRepositoryInterface;
use core\domain\TaskRepositoryInterface;
use core\domain\TaskServiceInterface;

class TaskService implements TaskServiceInterface
{
    private TaskRepositoryInterface $taskRepository;
    private TaskLinkRepositoryInterface $taskLinkRepository;

    public function __construct(
        TaskRepositoryInterface $taskRepository,
        TaskLinkRepositoryInterface $taskLinkRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->taskLinkRepository = $taskLinkRepository;
    }

    public function getOrCreateSource(ExternalTaskDTO $dto): ?Task
    {
        $task = $this->taskRepository->findBySystemAndId($dto->sourceSystem, $dto->id);

        if (!$task) {
            $task = new Task();
            $task->setExternalSystem($dto->sourceSystem);
            $task->setExternalId($dto->id);
        }

        $task->setName($dto->name);
        $task->setIsCompleted($dto->isCompleted);

        return $task;
    }

    public function getOrCreateTarget(int $id, string $targetSystem, ExternalTaskDTO $dto): ?Task
    {
        $task = $this->taskRepository->findBySystemAndSourceTask($targetSystem, $id);

        if (!$task) {
            $task = new Task();
            $task->setExternalSystem($targetSystem);
            $task->setName($dto->name);
            $task->setIsCompleted($dto->isCompleted);
        }

        return $task;
    }

    public function saveSource(Task $task): void
    {
        $this->save($task);
    }

    public function saveTarget(Task $sourceTask, Task $targetTask): void
    {
        $this->taskLinkRepository->beginTransaction();

        try {
            $this->save($targetTask);
            $this->updateTaskLink($sourceTask, $targetTask);

            $this->taskLinkRepository->commit();
        } catch (\Exception $e) {
            $this->taskLinkRepository->rollBack();

            throw $e;
        }
    }

    public function deleteTarget(Task $sourceTask, Task $targetTask): void
    {
        $this->taskLinkRepository->beginTransaction();

        try {
            $this->delete($targetTask);
            $this->updateTaskLink($sourceTask, $targetTask);

            $this->taskLinkRepository->commit();
        } catch (\Exception $e) {
            $this->taskLinkRepository->rollBack();

            throw $e;
        }
    }

    private function updateTaskLink(Task $sourceTask, Task $targetTask): void
    {
        $taskLink = $this->taskLinkRepository->findBySourceAndTarget($sourceTask, $targetTask);

        if (!$taskLink) {
            $taskLink = new TaskLink($sourceTask, $targetTask);
        } else {
            $taskLink->updateLastSyncedAt();
        }

        $this->taskLinkRepository->updateTaskLink($taskLink);
    }

    private function save(Task $task): void
    {
        $this->taskRepository->save($task);
    }

    private function delete(Task $task): void
    {
        $task->setDeleted();

        $this->taskRepository->save($task);
    }
}
