<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Migration\NeoMigration;

class DataRefreshCommand extends Command
{
    protected static $defaultName = 'app:data:refresh';

    /**
     * @var NeoMigration
     */
    protected $migrator;

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setDescription('Refresh app data.');
    }

    public function __construct(NeoMigration $migrator)
    {
        $this->migrator = $migrator;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->migrator->migrate();

        $io->writeln('Done');
    }
}
