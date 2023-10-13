<?php

namespace App\Command;

use App\Synchronizer\FighterSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:sync',
    description: 'Synchronize database with current ufcstats.com\'s data',
)]
class SynchronizeCommand extends Command
{
    public static $defaultName = 'app:sync';

    public function __construct(private readonly FighterSynchronizer $fighterSynchronizer)
    {
        parent::__construct(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fighterSynchronizer->sync();

        return Command::SUCCESS;
    }
}
