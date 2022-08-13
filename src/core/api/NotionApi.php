<?php

namespace core\api;

use core\App;
use core\exception\ApiException;

/**
 *
 */
class NotionApi
{
    /**
     * @param $databaseId
     * @return array
     * @throws ApiException
     */
    public function retrieve($databaseId): array
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

            $params = '{
                "start_cursor": "' . $lastItem->id . '"
            }';

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

        $params = '{
            "properties": {
                "Done": { "checkbox": ' . $value . ' }
            }
        }';

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

        $params = '{
            "properties": {
                "Name": { "title": ' . $name . ' }
            }
        }';

        $this->request($link, $params, 'PATCH');
    }

    /**
     * @param $link
     * @param $params
     * @param $method
     * @return mixed
     * @throws ApiException
     */
    private function request($link, $params, $method = 'POST')
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
