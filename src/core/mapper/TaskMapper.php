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
     * @param int $id
     * @return Task|null
     * @throws AppException
     */
    public function findParent(int $id): ?Task
    {
        return $this->performFind([$id], $this->selectParentStmt);
    }

    /**
     * @param int $parentId
     * @param string $system
     * @return Task|null
     * @throws AppException
     */
    public function findConnected(int $parentId, string $system): ?Task
    {
        return $this->performFind([$parentId, $system], $this->selectConnectedStmt);
    }

    /**
     * @param string $id
     * @param string $system
     * @return Task|null
     * @throws AppException
     */
    public function find(string $id, string $system): ?Task
    {
        return $this->performFind([$id, $system], $this->selectStmt);
    }

    /**
     * @param array $params
     * @param PDOStatement $statement
     * @return Task|null
     * @throws AppException
     */
    private function performFind(array $params, PDOStatement $statement): ?Task
    {
        $statement->execute($params);
        $row = $statement->fetch();
        $statement->closeCursor();

        if (!is_array($row)) {
            return null;
        }

        if (!isset($row['id'])) {
            return null;
        }

        return $this->createObject($row);
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