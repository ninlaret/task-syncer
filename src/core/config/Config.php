<?php

namespace core\config;

class Config
{
    static public function getDefaultConfig(): array
    {
        return [
            'dbName' => 'tasker',
            'dbUser' => 'user',
            'dbPassword' => 'password',

            'table' => 'task',

            'logPath' => dirname(__FILE__) . '/../../../../../../debug.log',
            'showLogs' => true,
            'logger' => 'core\EchoLogger',
            'defaultLogger' => 'core\EchoLogger',

            'apiRealisations' => [
                'notion' => 'core\system\NotionApiSystem',
                'habitica' => 'core\system\HabiticaApiSystem',
                'gitlab' => 'core\system\GitlabApiSystem'
            ],

            'syncParams' => [
                'target' => []
            ]
        ];
    }
}