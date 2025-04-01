<?php

namespace core\infrastructure\repository;

use core\domain\entity\Task;
use core\domain\entity\TaskLink;
use core\domain\TaskRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTaskRepository implements TaskRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findBySystemAndId(string $externalSystem, string $externalId): ?Task
    {
        return $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.externalSystem = :externalSystem')
            ->andWhere('t.externalId = :externalId')
            ->setParameter('externalSystem', $externalSystem)
            ->setParameter('externalId', $externalId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySystemAndSourceTask(string $externalSystem, int $id): ?Task
    {
        return $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(Task::class, 't')
            ->innerJoin(TaskLink::class, 'tl', 'WITH', 'tl.targetTask = t')
            ->where('tl.sourceTask = :id')
            ->andWhere('t.externalSystem = :externalSystem')
            ->setParameter('id', $id)
            ->setParameter('externalSystem', $externalSystem)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Task $task): void
    {
        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }

    public function delete(Task $task): void
    {
        $task->setDeleted();

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}