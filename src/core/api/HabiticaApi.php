<?php

namespace core\api;

use core\App;
use core\exception\ApiException;

/**
 *
 */
class HabiticaApi
{
    /**
     * @param $name
     * @return string
     * @throws ApiException
     */
    public function send($name): string
    {
        $params = new \stdClass();
        $params->text = addslashes($name);
        $params->type = 'todo';

        $result = $this->request('tasks/' . 'user', json_encode($params));

        return $result->data->_id;
    }

    /**
     * @param string $id
     * @param bool $completed
     * @return void
     * @throws ApiException
     */
    public function updateCompleted(string $id, bool $completed): void
    {
        $direction = $completed ? 'up' : 'down';
        $this->request('tasks/' . $id . '/score/' . $direction);
    }

    /**
     * @param string $id
     * @param string $name
     * @return void
     * @throws ApiException
     */
    public function updateName(string $id, string $name): void
    {
        $params = new \stdClass();
        $params->text = addslashes($name);

        $this->request('tasks/' . $id, json_encode($params), 'PUT');
    }

    /**
     * @param $id
     * @return mixed
     * @throws ApiException
     */
    public function delete($id)
    {
        return $this->request('tasks/' . $id, false, 'DELETE');
    }

    /**
     * @param $method
     * @param $params
     * @param $requestMethod
     * @return mixed
     * @throws ApiException
     */
    private function request($method, $params = false, $requestMethod = 'POST')
    {
        $url = App::$config['habiticaLink'] . $method;
        $ch = curl_init($url);

        $headersArray = array();
        $headersArray[] = 'x-api-user: ' . App::$config['habiticaUserId'];
        $headersArray[] = 'x-api-key: ' . App::$config['habiticaToken'];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $curlMethod = $requestMethod;

        if ($params) {
            $headersArray[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        } else {
            $headersArray[] = 'Content-length: ' . 0;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersArray);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $curlMethod);

        $response = curl_exec($ch);

        //https://habitica.fandom.com/wiki/Guidance_for_Comrades#Rate_Limiting
        sleep(2);

        if (curl_errno($ch)) {
            throw new ApiException('Habitica curl error. ' . curl_error($ch));
        }

        curl_close($ch);

        $outputArray = json_decode($response);

        if (!isset($outputArray->success) || $outputArray->success === false) {
            $errorMessage = $outputArray->message ?? '';
            throw new ApiException('Habitica request error. ' . $errorMessage);
        }

        return $outputArray;
    }
}

?>
