<?php

namespace core\controller;

use core\App;
use core\service\TaskService;
use core\exception\AppException;
use core\exception\ApiException;

/**
 *
 */
class CliController extends Controller
{
    /**
     * @return void
     */
    public function syncAction(): void
    {
        $taskService = new TaskService(App::$config['syncParams'], App::$config['apiRealisations']);
        App::$logger->log('Getting tasks from sources...');
        $tasks = [];

        try {
            $tasks = $taskService->getAllTasksFromSources();
        } catch (AppException|ApiException $exception) {
            App::$logger->error($exception->getMessage());
        }

        $message = count($tasks) ? 'Found ' . count($tasks) . ' tasks. Syncing...' : 'No tasks found';
        App::$logger->log($message);

        foreach ($tasks as $task) {
            try {
                $taskService->syncWithTargets($task);
            } catch (AppException|ApiException $exception) {
                App::$logger->error($exception->getMessage());
            }
        }
    }
}