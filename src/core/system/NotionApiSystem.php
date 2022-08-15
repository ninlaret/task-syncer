<?php

namespace core\system;

use core\api\NotionApi;
use core\exception\AppException;

/**
 *
 */
class NotionApiSystem extends ApiSystem
{
    /**
     * @var string
     */
    protected string $databaseId = '';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'notion';
    }

    /**
     * @return object
     */
    public function getApi(): object
    {
        if (!isset($this->api)) {
            $this->api = new NotionApi();
        }

        return $this->api;
    }

    /**
     * @return array
     * @throws AppException
     */
    public function getAllTasks(): array
    {
        $rawTasks = $this->getApi()->retrieve($this->databaseId);
        $tasks = array();

        foreach ($rawTasks as $rawTask) {
            if (isset($rawTask->id) && isset($rawTask->properties->Name) && isset($rawTask->properties->Name->title[0])) {
                $tasks[] = $this->makeTask($rawTask->id, $rawTask->properties->Name->title[0]->plain_text, $rawTask->properties->Done->checkbox);
            }
        }

        return $tasks;
    }

    /**
     * @param string|int $id
     * @param bool $completed
     * @return void
     */
    public function updateCompleted(string|int $id, bool $completed): void
    {
        $this->getApi()->updateCompleted($id, $completed);
    }

    /**
     * @param string|int $id
     * @param string $name
     * @return void
     */
    public function updateName(string|int $id, string $name): void
    {
        $this->getApi()->updateName($id, $name);
    }

    /**
     * @param string $name
     * @param bool $completed
     * @return int
     */
    public function sendTask(string $name, bool $completed = false): int
    {
        $id = time() . rand(1, 9999);

        if ($completed) {
            $this->updateCompleted($id, $completed);
        }

        return $id;
    }
}