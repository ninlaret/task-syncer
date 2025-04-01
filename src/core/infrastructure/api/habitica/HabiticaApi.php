<?php

namespace core\infrastructure\api\habitica;

use core\domain\entity\Task;
use core\domain\TaskApiFetchInterface;
use core\domain\TaskApiUpdateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HabiticaApi implements TaskApiUpdateInterface, TaskApiFetchInterface
{
    private Client $client;

    private int $requestsPerMinute = 5;
    private int $requestsMade = 0;

    public function __construct(Client $httpClient)
    {
        $this->client = $httpClient;
    }

    private function applyThrottling(): void
    {
        $this->requestsMade++;

        if ($this->requestsMade > $this->requestsPerMinute) {
            $sleepTime = 60 - time() % 60;
            sleep($sleepTime);
            $this->requestsMade = 0;
        }
    }

    public function fetchTasks(): array
    {
        $result = [];

        try {
            $this->applyThrottling();
            $response = $this->client->get('tasks/user');

            $data = json_decode($response->getBody()->getContents());

            if (isset($data->data)) {
                $result = array_merge($result, $data->data);
            }

        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $result;
    }

    public function updateTaskStatus(string $taskId, bool $isCompleted): void
    {
        $direction = $isCompleted ? 'up' : 'down';

        try {
            $this->applyThrottling();
            $this->client->post(sprintf('tasks/%s/score/%s', $taskId, $direction));
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    public function updateTaskName(string $taskId, string $name): void
    {
        $params = [
            'text' => $name,
        ];

        try {
            $this->applyThrottling();
            $this->client->put(sprintf('tasks/%s', $taskId), [
                'json' => $params,
            ]);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    public function createTask(Task $task): string
    {
        $params = [
            'text' => addslashes($task->getName()),
            'type' => 'todo',
        ];

        try {
            $this->applyThrottling();
            $response = $this->client->post('tasks/user', [
                'json' => $params,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $data['data']['_id'];
    }

    public function deleteTask(Task $task): void
    {
        try {
            $this->applyThrottling();
            $this->client->delete(sprintf('tasks/%s', $task->getId()));
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    private function handleRequestException(RequestException $e): void
    {
        $response = $e->getResponse();
        $message = $e->getMessage();

        if ($response) {
            $body = $response->getBody()->getContents();
            $message .= sprintf(' Response: %s', $body);
        }

        throw new \RuntimeException(sprintf('Habitica API error: %s', $message));
    }
}