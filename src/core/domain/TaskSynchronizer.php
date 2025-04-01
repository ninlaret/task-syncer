<?php

namespace core\domain;

use core\application\ExternalTaskDTO;
use core\domain\collection\TaskFetchersCollection;
use core\domain\collection\TaskMappersCollection;
use core\domain\strategy\TaskSyncStrategyFactory;

class TaskSynchronizer
{
    private array $taskMappers;
    private array $externalTaskFetchers;
    private TaskSyncStrategyFactory $taskSyncStrategyFactory;
    private array $syncRoutes;
    private TaskServiceInterface $taskService;

    public function __construct(
        TaskMappersCollection $taskMappers,
        TaskFetchersCollection  $taskFetchersCollection,
        TaskSyncStrategyFactory $taskSyncStrategyFactory,
        array                   $syncRoutes,
        TaskServiceInterface    $taskService,
    ) {
        $this->taskMappers = $taskMappers->getMappers();
        $this->externalTaskFetchers = $taskFetchersCollection->getFetchers();
        $this->taskSyncStrategyFactory = $taskSyncStrategyFactory;
        $this->syncRoutes = $syncRoutes;
        $this->taskService = $taskService;
    }

    public function synchronize(): void
    {
        foreach ($this->syncRoutes as $sourceSystem => $targetSystems) {
            $externalTasks = $this->externalTaskFetchers[$sourceSystem]->fetchTasks();

            /** @var ExternalTaskDTO[] $dtos */
            $dtos = array_map(
                fn($externalTask) => $this->taskMappers[$sourceSystem]->mapExternalToDTO($externalTask),
                $externalTasks
            );

            foreach ($dtos as $dto) {
                $sourceTask = $this->taskService->getOrCreateSource($dto);
                $this->taskService->saveSource($sourceTask);

                foreach ($targetSystems as $targetSystem) {
                    $targetTask = $this->taskService->getOrCreateTarget($sourceTask->getId(), $targetSystem, $dto);

                    $strategy = $this->taskSyncStrategyFactory->createStrategy($sourceTask, $targetTask);
                    $strategy->execute($sourceTask, $targetTask);
                }
            }
        }
    }
}