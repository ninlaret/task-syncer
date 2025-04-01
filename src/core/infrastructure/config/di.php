<?php

use core\application\DbInitCommand;
use core\application\SyncCommand;
use core\domain\collection\TaskFetchersCollection;
use core\domain\collection\TaskMappersCollection;
use core\domain\collection\TaskUpdatersCollection;
use core\domain\strategy\TaskSyncStrategyFactory;
use core\domain\TaskSynchronizer;
use core\infrastructure\config\DoctrineConfig;
use core\infrastructure\repository\DoctrineTaskLinkRepository;
use core\infrastructure\repository\DoctrineTaskRepository;
use core\infrastructure\service\DbInitializer;
use core\infrastructure\service\TaskService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

return [
    TaskSynchronizer::class => function ($container) {
        return new TaskSynchronizer(
            $container->get(TaskMappersCollection::class),
            $container->get(TaskFetchersCollection::class),
            $container->get(TaskSyncStrategyFactory::class),
            $container->get('syncRoutes'),
            $container->get(TaskService::class),
        );
    },

    DbInitCommand::class => function ($container) {
        return new DbInitCommand($container->get(DbInitializer::class));
    },

    DbInitializer::class => function ($container) {
        $entityManager = $container->get(EntityManagerInterface::class);
        return new DbInitializer($entityManager);
    },

    SyncCommand::class => function ($container) {
        return new SyncCommand($container->get(TaskSynchronizer::class));
    },

    'commands' => [
        SyncCommand::class,
        DbInitCommand::class,
    ],

    DoctrineConfig::class => function ($container) {
        $basePath = $container->get('base_path');

        $cacheDir = $basePath . '/var/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $cache = new FilesystemAdapter('', 0, $cacheDir);

        return new DoctrineConfig(
            $container->get('database')['dbname'],
            $container->get('database')['user'],
            $container->get('database')['password'],
            $container->get('database')['host'],
            $container->get('database')['driver'],
            false,
            $cache
        );
    },

    EntityManagerInterface::class => function ($container) {
        $doctrineConfig = $container->get(DoctrineConfig::class);

        return $doctrineConfig->getEntityManager();
    },

    TaskService::class => function ($container) {
        return new TaskService(
            $container->get(DoctrineTaskRepository::class),
            $container->get(DoctrineTaskLinkRepository::class)
        );
    },

    TaskSyncStrategyFactory::class => function ($container) {
        return new TaskSyncStrategyFactory(
            $container->get(TaskService::class),
            $container->get(TaskUpdatersCollection::class)
        );
    },

    DoctrineTaskRepository::class => function ($container) {
        return new DoctrineTaskRepository($container->get(EntityManagerInterface::class));
    },

    DoctrineTaskLinkRepository::class => function ($container) {
        return new DoctrineTaskLinkRepository($container->get(EntityManagerInterface::class));
    },

    AdapterInterface::class => function () {
        return new FilesystemAdapter('', 0, __DIR__ . '/cache');
    },

    TaskUpdatersCollection::class => function ($container) {
        return new TaskUpdatersCollection($container->get('updaters'));
    },

    TaskFetchersCollection::class => function ($container) {
        return new TaskFetchersCollection($container->get('fetchers'));
    },

    TaskMappersCollection::class => function ($container) {
        return new TaskMappersCollection($container->get('mappers'));
    }
];