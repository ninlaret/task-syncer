<?php

namespace core\mapper;

use core\App;
use core\domain\Task;
use core\exception\AppException;
use PDO;
use PDOStatement;

/**
 *
 */
class TaskMapper
{
    /**
     * @var PDOStatement|false
     */
    private PDOStatement|bool $selectStmt;
    /**
     * @var PDOStatement|false
     */
    private PDOStatement|bool $selectConnectedStmt;
    /**
     * @var PDOStatement|false
     */
    private PDOStatement|bool $selectParentStmt;
    /**
     * @var PDOStatement|false
     */
    private PDOStatement|bool $updateStmt;
    /**
     * @var PDOStatement|false
     */
    private PDOStatement|bool $insertStmt;
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     *
     */
    public function __construct()
    {
        $table = App::$config['table'];
        $this->pdo = App::$db;

        $this->selectStmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE `system_id` = ? AND `system` = ?");
        $this->selectConnectedStmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE `parent_id` = ? AND `system` = ?");
        $this->selectParentStmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE `id` = ?");
        $this->updateStmt = $this->pdo->prepare("UPDATE {$table} SET `name` = ?, `is_completed` = ?, `parent_id` = ? WHERE `system` = ? AND `system_id` = ?");
        $this->insertStmt = $this->pdo->prepare("INSERT INTO {$table} (`name`, `is_completed`, `system`, `system_id`, `parent_id`) VALUES (?, ?, ?, ?, ?)");
    }

    /**
     * @param Task $task
     * @param int|null $parentId
     * @return void
     */
    public function insert(Task $task, ?int $parentId = null): void
    {
        $values = [
            $task->getName(),
            $task->getCompleted(),
            $task->getSystem(),
            $task->getExternalId(),
            $parentId,
        ];

        $this->insertStmt->execute($values);
        $id = $this->pdo->lastInsertId();

        $task->setLocalId((int)$id);

        if ($parentId === null) {
            $task->setParentId((int)$id);
            $this->update($task);
        }
    }

    /**
     * @param int $parentId
     * @return Task|null
     */
    public function findParent(int $parentId): ?Task
    {
        $values = [$parentId];
        $this->selectParentStmt->execute($values);
        $row = $this->selectParentStmt->fetch();
        $this->selectParentStmt->closeCursor();

        if (!is_array($row)) {
            return null;
        }

        if (!isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
    }

    /**
     * @param int $parentId
     * @param string $system
     * @return Task|null
     */
    public function findConnected(int $parentId, string $system): ?Task
    {
        $values = [$parentId, $system];
        $this->selectConnectedStmt->execute($values);
        $row = $this->selectConnectedStmt->fetch();
        $this->selectConnectedStmt->closeCursor();

        if (!is_array($row)) {
            return null;
        }

        if (!isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
    }

    /**
     * @param string $id
     * @param string $system
     * @return Task|null
     */
    public function find(string $id, string $system): ?Task
    {
        $values = [$id, $system];
        $this->selectStmt->execute($values);
        $row = $this->selectStmt->fetch();
        $this->selectStmt->closeCursor();

        if (!is_array($row)) {
            return null;
        }

        if (!isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
    }

    /**
     * @param Task $task
     * @return void
     */
    public function update(Task $task): void
    {
        $values = [
            $task->getName(),
            $task->getCompleted(),
            $task->getParentId(),
            $task->getSystem(),
            $task->getExternalId()
        ];

        $this->updateStmt->execute($values);
        $task->setUpdated();
    }

    /**
     * @param array $raw
     * @return Task
     * @throws AppException
     */
    protected function createObject(array $raw): Task
    {
        $task = new Task($raw['id'], $raw['system_id'], $raw['name'], $raw['is_completed'], $raw['system']);
        $task->setParentId($raw['parent_id']);
        return $task;
    }
}