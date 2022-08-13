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
        $taskService = TaskService::getInstance();

        try {
            App::$logger->log('Getting tasks from sources...');
            $tasks = $taskService->getAllTasksFromSources();

            App::$logger->log('Found ' . count($tasks) . ' tasks. Syncing...');

            foreach ($tasks as $task) {
                $taskService->syncWithTargets($task);
            }

            App::$logger->log('Done.');

        } catch (AppException|ApiException $exception) {
            App::$logger->error($exception->getMessage());
        }
    }
}