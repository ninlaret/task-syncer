<?php

namespace core\api;

use core\App;
use core\exception\ApiException;

/**
 *
 */
class GitlabApi
{
    /**
     * @param string $name
     * @param string $description
     * @return string
     * @throws ApiException
     */
    public function send(string $name, string $description = ''): string
    {
        return $this->request('issues', ['title' => $name, 'description' => $description, 'assignee_id' => 14])->iid;
    }

    /**
     * @param string $id
     * @param string $name
     * @return void
     * @throws ApiException
     */
    public function updateName(string $id, string $name): void
    {
        $this->request('issues/' . $id, ['title' => $name], 'PUT');
    }

    /**
     * @param string $id
     * @return void
     * @throws ApiException
     */
    public function complete(string $id): void
    {
        $this->request('issues/' . $id, ['state_event' => 'close'], 'PUT');
    }

    /**
     * @param string $id
     * @return void
     * @throws ApiException
     */
    public function reopen(string $id): void
    {
        $this->request('issues/' . $id, ['state_event' => 'reopen'], 'PUT');
    }

    /**
     * @param string $method
     * @param array $params
     * @param string $requestMethod
     * @return mixed
     * @throws ApiException
     */
    private function request(string $method, array $params, string $requestMethod = 'POST'): object
    {
        $url = App::$config['gitlabLink'] . $method . '?' . http_build_query($params);
        $ch = curl_init($url);

        $headersArray = array();
        $headersArray[] = 'PRIVATE-TOKEN: ' . App::$config['gitlabToken'];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlMethod = $requestMethod;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArray);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $curlMethod);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new ApiException('Gitlab curl error: ' . curl_error($ch));
        }

        curl_close($ch);
        $outputArray = json_decode($response);

        if (!isset($outputArray)) {
            throw new ApiException('Error on gitlab task syncing');
        }

        return $outputArray;
    }
}
