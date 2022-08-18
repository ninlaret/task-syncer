<?php

namespace core\system;

use core\api\HabiticaApi;
use core\domain\Task;
use core\exception\AppException;

/**
 *
 */
class HabiticaApiSystem extends ApiSystem
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'habitica';
    }

    /**
     * @return object
     */
    public function getApi(): object
    {
        if (!isset($this->api)) {
            $this->api = new HabiticaApi();
        }

        return $this->api;
    }

    /**
     * @return array
     */
    public function getAllTasks(): array
    {
        return array();
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
     * @return Task
     * @throws AppException
     */
    public function create(string $name, bool $completed = false): Task
    {
        $id = $this->getApi()->send($name);

        if ($completed) {
            $this->updateCompleted($id, $completed);
        }

        return $this->makeTask($id, $name, $completed);
    }
}
