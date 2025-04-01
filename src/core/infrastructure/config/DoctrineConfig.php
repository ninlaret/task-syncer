<?php

namespace core\infrastructure\config;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Cache\CacheItemPoolInterface;

class DoctrineConfig
{
    private string $dbName;
    private string $user;
    private string $password;
    private string $host;
    private string $driver;
    private bool $isDevMode;
    private CacheItemPoolInterface $cache;

    public function __construct(string $dbName, string $user, string $password, string $host, string $driver, bool $isDevMode, CacheItemPoolInterface $cache)
    {
        $this->dbName = $dbName;
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->driver = $driver;
        $this->isDevMode = $isDevMode;
        $this->cache = $cache;
    }

    public function getConnectionParams(): array
    {
        return [
            'dbname' => $this->dbName,
            'user' => $this->user,
            'password' => $this->password,
            'host' => $this->host,
            'driver' => $this->driver,
        ];
    }

    public function getEntityManager(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../entity'],
            $this->isDevMode
        );

        $config->setMetadataCache($this->cache);
        $config->setQueryCache($this->cache);
        $config->setResultCache($this->cache);

        $connectionParams = $this->getConnectionParams();
        $connection = DriverManager::getConnection($connectionParams, $config);

        return new EntityManager($connection, $config);
    }
}