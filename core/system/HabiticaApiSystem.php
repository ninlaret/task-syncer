<?php

namespace core\system;

use core\api\HabiticaApi;

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
     * @return string|int
     */
    public function sendTask(string $name, bool $completed = false): string|int
    {
        $id = $this->getApi()->send($name);

        if ($completed) {
            $this->updateCompleted($id, $completed);
        }

        return $id;
    }
}
