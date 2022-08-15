<?php

namespace core\api;

use core\App;
use core\exception\ApiException;
use stdClass;

/**
 *
 */
class NotionApi
{
    /**
     * @param string $databaseId
     * @return array
     * @throws ApiException
     */
    public function retrieve(string $databaseId): array
    {
        $result = array();
        $params = false;
        $link = 'databases/' . $databaseId . '/' . 'query';

        do {
            $items = $this->request($link, $params);

            $lastItem = end($items->results);

            if ($params !== false) {
                unset($items->results[0]);
            }

            $paramsObject = new stdClass();
            $paramsObject->start_cursor = $lastItem->id;
            $params = json_encode($paramsObject);

            $result = array_merge($result, $items->results);

        } while (count($items->results) > 0);

        return $result;
    }

    /**
     * @param string $id
     * @param bool $completed
     * @return void
     * @throws ApiException
     */
    public function updateCompleted(string $id, bool $completed): void
    {
        $value = $completed ? 'true' : false;
        $link = 'pages/' . $id;

        $paramsObject = new stdClass();
        $properties = new stdClass();
        $valueObject = new stdClass();
        $valueObject->checkbox = $value;
        $properties->Done = $valueObject;
        $paramsObject->properties = $properties;

        $params = json_encode($paramsObject);

        $this->request($link, $params, 'PATCH');
    }

    /**
     * @param string $id
     * @param string $name
     * @return void
     * @throws ApiException
     */
    public function updateName(string $id, string $name): void
    {
        $link = 'pages/' . $id;

        $paramsObject = new stdClass();
        $properties = new stdClass();
        $valueObject = new stdClass();
        $valueObject->title = $name;
        $properties->Name = $valueObject;
        $paramsObject->properties = $properties;

        $params = json_encode($paramsObject);

        $this->request($link, $params, 'PATCH');
    }

    /**
     * @param string $link
     * @param string|bool $params
     * @param string $method
     * @return mixed
     * @throws ApiException
     */
    private function request(string $link, string|bool $params, string $method = 'POST'): object
    {
        $url = App::$config['notionLink'] . $link;

        $ch = curl_init($url);

        $headersArray = array();
        $headersArray[] = 'Authorization: Bearer ' . App::$config['notionToken'];
        $headersArray[] = 'Notion-Version: 2021-08-16';
        $headersArray[] = 'Content-Type: application/json';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArray);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($params !== false) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new ApiException('Notion curl error. ' . curl_error($ch));
        }

        curl_close($ch);
        $outputArray = json_decode($response);

        if (isset($outputArray->object) && $outputArray->object === 'error') {
            $errorMessage = $outputArray->message ?? '';
            throw new ApiException('Notion request error. ' . $errorMessage);
        }

        return $outputArray;
    }
}
