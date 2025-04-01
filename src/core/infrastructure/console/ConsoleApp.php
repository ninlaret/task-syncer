<?php

namespace core\infrastructure\console;

use DI\Container;
use Symfony\Component\Console\Application;

class ConsoleApp
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function run(): void
    {
        $app = new Application();
        $app->setDefaultCommand('list');

        foreach ($this->container->get('commands') as $commandClass) {
            $app->add($this->container->get($commandClass));
        }

        $app->run();
    }
}