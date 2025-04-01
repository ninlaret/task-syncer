<?php

namespace core\application;

use core\domain\TaskSynchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    private TaskSynchronizer $taskSynchronizer;

    public function __construct(TaskSynchronizer $taskSynchronizer)
    {
        parent::__construct();

        $this->taskSynchronizer = $taskSynchronizer;
    }

    protected function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Sync tasks between different task systems');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting task synchronization...');

        $this->taskSynchronizer->synchronize();

        $output->writeln('Tasks synchronized successfully!');

        return Command::SUCCESS;
    }
}