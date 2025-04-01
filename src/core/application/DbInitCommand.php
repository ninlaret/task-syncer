<?php

namespace core\application;

use core\infrastructure\service\DbInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DbInitCommand extends Command
{
    private DbInitializer $dbInitializer;

    public function __construct(DbInitializer $dbInitializer)
    {
        parent::__construct();
        $this->dbInitializer = $dbInitializer;
    }

    protected function configure(): void
    {
        $this->setName('init')
            ->setDescription('Initialize the database and create required tables.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $output->writeln('Initializing database...');
            $this->dbInitializer->initialize();
            $output->writeln('Database initialized successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Error initializing database: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}