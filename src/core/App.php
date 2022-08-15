<?php

namespace core;

use core\config\Config;
use core\exception\AppException;
use core\logger\Logger;
use core\service\SystemService;
use core\installer\Installer;
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
     * @param array $config
     * @param array $params
     * @param string $mode
     * @return void
     */
    public static function init(array $config, array $params, string $mode): void
    {
        self::$config = self::makeConfig($config, $params);
        self::$logger = self::getLogger(self::$config['logger']);

        self::$logger->log('Initializing...');

        try {
            self::initParameters(self::$config);

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
     * @param array $config
     * @return void
     * @throws AppException
     */
    private static function initParameters(array $config): void
    {
        self::$db = self::setDb();

        $installer = Installer::getInstance();
        if (!$installer->checkInstalled(self::$config['table'])) {

            self::$logger->log('Installing db table...');
            $installer->install(self::$config['table']);
            self::$logger->log('Done. Don\'t forget to edit cli and web configs in the /config directory and fill your api keys in params file');
        }

        self::$assembler = new SystemAssembler($config['apiRealisations']);
        SystemService::init($config['syncParams']['target']);
    }

    /**
     * @param array $config
     * @param array $params
     * @return array
     */
    private static function makeConfig(array $config, array $params): array
    {
        $appConfig = Config::getDefaultConfig();

        return $config + $params + $appConfig;
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
     * @param string $mode
     * @return object
     */
    private static function getController(string $mode): object
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
     * @param string $logger
     * @return Logger
     */
    private static function getLogger(string $logger): Logger
    {
        $class = class_exists($logger) ? $logger : self::$config['defaultLogger'];

        return $class::getInstance();
    }
}