<?php

namespace core\infrastructure\repository;

use core\domain\entity\Task;
use core\domain\entity\TaskLink;
use core\domain\TaskLinkRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTaskLinkRepository implements TaskLinkRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createTaskLink(TaskLink $taskLink): void
    {
        $this->entityManager->persist($taskLink);
    }

    public function updateTaskLink(TaskLink $taskLink): void
    {
        $this->entityManager->persist($taskLink);
    }

    public function findBySourceAndTarget(Task $sourceTask, Task $targetTask): ?TaskLink
    {
        return $this->entityManager->createQueryBuilder()
            ->select('tl')
            ->from(TaskLink::class, 'tl')
            ->where('tl.sourceTask = :sourceTask')
            ->andWhere('tl.targetTask = :targetTask')
            ->setParameter('sourceTask', $sourceTask)
            ->setParameter('targetTask', $targetTask)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function rollBack(): void
    {
        $this->entityManager->rollback();
    }
}