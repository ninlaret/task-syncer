<?php

namespace core\infrastructure\api\notion;

use core\domain\entity\Task;
use core\domain\TaskApiFetchInterface;
use core\domain\TaskApiUpdateInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NotionApi implements TaskApiUpdateInterface, TaskApiFetchInterface
{
    private Client $client;
    private string $databaseId;
    private string $donePropertyName;

    public function __construct(Client $httpClient, string $databaseId, string $donePropertyName)
    {
        $this->client = $httpClient;
        $this->databaseId = $databaseId;
        $this->donePropertyName = $donePropertyName;
    }

    public function fetchTasks(): array
    {
        $params = false;
        $result = [];

        try {
            do {
                $response = $this->client->post(sprintf('databases/%s/query', $this->databaseId),
                    $params ? ['json' => $params] : []
                );

                $data = json_decode($response->getBody()->getContents());

                if (isset($data->results)) {
                    $result = array_merge($result, $data->results);
                    $params = ['start_cursor' => $data->next_cursor];
                }

            } while ($data->has_more === true);

        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $result;
    }

    public function updateTaskStatus(string $taskId, bool $isCompleted): void
    {
        $paramsObject = [
            'properties' => [
                $this->donePropertyName => [
                    'checkbox' => $isCompleted,
                ]
            ]
        ];

        try {
            $this->client->patch(sprintf('pages/%s', $taskId), [
                'json' => $paramsObject,
            ]);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    public function updateTaskName(string $taskId, string $name): void
    {
        $paramsObject = [
            'properties' => [
                'Name' => [
                    'title' => [
                        [
                            'type' => 'text',
                            'text' => [
                                'content' => $name
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $this->client->patch(sprintf('pages/%s', $taskId), [
                'json' => $paramsObject,
            ]);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    public function createTask(Task $task): string
    {
        $paramsObject = [
            'parent' => ['database_id' => $this->databaseId],
            'properties' => [
                'Name' => [
                    'title' => [
                        [
                            'text' => ['content' => $task->getName()]
                        ]
                    ]
                ],
                'Done' => [
                    'checkbox' => $task->isCompleted()
                ]
            ]
        ];

        try {
            $response = $this->client->post('pages', [
                'json' => $paramsObject,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }

        return $data['id'];
    }

    public function deleteTask(Task $task): void
    {
        try {
            $this->client->delete(sprintf('pages/%s', $task->getId()));
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
            $message = sprintf('%s Response: %s', $message, $body);
        }

        throw new \RuntimeException(sprintf('Notion API error: %s', $message));
    }
}