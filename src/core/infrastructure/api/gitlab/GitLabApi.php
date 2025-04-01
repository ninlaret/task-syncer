<?php

namespace core\infrastructure\api\gitlab;

use core\domain\entity\Task;
use core\domain\TaskApiFetchInterface;
use core\domain\TaskApiUpdateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class GitLabApi implements TaskApiUpdateInterface, TaskApiFetchInterface
{
    private Client $client;
    private string $projectId;

    public function __construct(Client $client, string $projectId)
    {
        $this->client = $client;
        $this->projectId = $projectId;
    }

    public function fetchTasks(): array
    {
        $allTasks = [];
        $page = 1;

        try {
            do {
                $response = $this->client->get(sprintf('projects/%s/issues', $this->projectId), [
                    'query' => ['page' => $page, 'per_page' => 100]
                ]);

                $tasks = json_decode($response->getBody()->getContents(), true);
                $allTasks = array_merge($allTasks, $tasks);

                $nextPage = $response->getHeader('X-Next-Page')[0] ?? null;
                $page = $nextPage ? (int) $nextPage : null;
            } while ($page);

            return $allTasks;
        } catch (RequestException $e) {
            throw new \RuntimeException(sprintf('GitLab API error while fetching tasks: %s', $e->getMessage()));
        }
    }

    public function updateTaskStatus(string $taskId, bool $isCompleted): void
    {
        try {
            $this->client->put(sprintf('projects/%s/issues/%s', $this->projectId, $taskId), [
                'json' => ['state_event' => $isCompleted ? 'close' : 'reopen'],
            ]);
        } catch (RequestException $e) {
            throw new \RuntimeException(sprintf('GitLab API error while updating task status: %s', $e->getMessage()));
        }
    }

    public function updateTaskName(string $taskId, string $name): void
    {
        try {
            $this->client->put(sprintf('projects/%s/issues/%s', $this->projectId, $taskId), [
                'json' => ['title' => $name],
            ]);
        } catch (RequestException $e) {
            throw new \RuntimeException(sprintf('GitLab API error while updating task name: %s', $e->getMessage()));
        }
    }

    public function createTask(Task $task): string
    {
        try {
            $response = $this->client->post(sprintf('projects/%s/issues', $this->projectId), [
                'json' => [
                    'title' => $task->getName()
                ],
            ]);

            $data = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            throw new \RuntimeException(sprintf('GitLab API error while creating task: %s', $e->getMessage()));
        }

        return $data->id;
    }

    public function deleteTask(Task $task): void
    {
        try {
            $this->client->delete(sprintf('issues/%s', $task->getId()));
        } catch (RequestException $e) {
            throw new \RuntimeException(sprintf('GitLab API error while deleting task: %s', $e->getMessage()));
        }
    }
}