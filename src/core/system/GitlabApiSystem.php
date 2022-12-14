<?php

namespace core\system;

use core\api\GitlabApi;
use core\domain\Task;
use core\exception\ApiException;
use core\exception\AppException;

/**
 *
 */
class GitlabApiSystem extends ApiSystem
{
    /**
     * @var object
     */
    private object $api;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'gitlab';
    }

    /**
     * @return GitlabApi
     */
    public function getApi(): GitlabApi
    {
        if (!isset($this->api)) {
            $this->api = new GitlabApi();
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
     * @throws ApiException
     */
    public function updateCompleted(string|int $id, bool $completed): void
    {
        if ($completed) {
            $this->getApi()->complete($id);
        } else {
            $this->getApi()->reopen($id);
        }
    }

    /**
     * @param string|int $id
     * @param string $name
     * @return void
     * @throws ApiException
     */
    public function updateName(string|int $id, string $name): void
    {
        $this->getApi()->updateName($id, $name);
    }

    /**
     * @param string $name
     * @param bool $completed
     * @return Task
     * @throws ApiException|AppException
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
