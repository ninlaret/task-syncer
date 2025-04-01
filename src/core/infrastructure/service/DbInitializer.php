<?php

namespace core\infrastructure\service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use core\domain\entity\Task;
use core\domain\entity\TaskLink;

class DbInitializer
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function initialize(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);

        $classes = [
            $this->entityManager->getClassMetadata(Task::class),
            $this->entityManager->getClassMetadata(TaskLink::class),
        ];

        $schemaTool->updateSchema($classes);
    }
}