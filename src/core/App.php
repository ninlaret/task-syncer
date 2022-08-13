<?php

namespace core;

use core\exception\AppException;
use core\logger\Logger;
use core\service\SystemService;
use PDO;
use PDOException;

/**
 *
 */
class App
{
    /**
     * @var array
     */
    public static array $config;
    /**
     * @var PDO
     */
    public static PDO $db;
    /**
     * @var SystemAssembler
     */
    public static SystemAssembler $assembler;
    /**
     * @var Logger
     */
    public static Logger $logger;

    /**
     * @param $config
     * @param $mode
     * @return void
     */
    public static function init($config, $mode): void
    {
        self::$config = self::makeConfig($config);
        self::$logger = self::getLogger(self::$config['logger']);

        self::$logger->log('Initializing...');

        try {
            self::$db = self::setDb();
            self::$assembler = new SystemAssembler(self::$config['apiRealisations']);
            SystemService::init($config['syncParams']['target']);

        } catch (AppException $exception) {
            self::$logger->error($exception->getMessage());
            return;
        }

        $action = (http_response_code() !== FALSE) ? self::parseWebAction() :
            ($_SERVER['argv'][1] ?? false);

        if ($action) {
            echo self::getController($mode)->run($action);
        } else {
            self::$logger->error('No action');
        }
    }

    /**
     * @param $config
     * @return array
     */
    private static function makeConfig($config): array
    {
        $appConfig = require(ROOT_PATH . 'config/app.php');
        $appLocalConfig = require(ROOT_PATH . 'config/app.local.php');

        return $config + $appLocalConfig + $appConfig;
    }

    /**
     * @return string
     */
    private static function parseWebAction(): string
    {
        $urlArray = explode('/', $_GET['url']);
        return $urlArray[0];
    }

    /**
     * @param $mode
     * @return object
     */
    private static function getController($mode): object
    {
        $controllerClass = match ($mode) {
            'Cli' => 'core\controller\CliController',
            'Web' => class_exists('app\controller\CustomController') ?
                'app\controller\CustomController' :
                'core\controller\Controller',
            default => 'core\controller\Controller',
        };

        return new $controllerClass();
    }

    /**
     * @return PDO
     * @throws AppException
     */
    private static function setDb(): PDO
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $db = new PDO('mysql:host=localhost;charset=utf8mb4;dbname=' . App::$config['dbName'], App::$config['dbUser'], App::$config['dbPassword'], $options);
        } catch(PDOException $e) {
            throw new AppException('Can\'t connect to database. ' . $e->getMessage());
        }

        return $db;
    }

    /**
     * @param $logger
     * @return Logger
     */
    private static function getLogger($logger): Logger
    {
        $class = class_exists($logger) ? $logger : self::$config['defaultLogger'];

        return $class::getInstance();
    }
}