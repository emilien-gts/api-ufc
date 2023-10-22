<?php

namespace App\Command;

use App\Synchronizer\Contracts\SynchronizerInterface;
use App\Synchronizer\FightSynchronizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'app:sync',
    description: 'Synchronize database with current ufcstats.com\'s data',
)]
class SynchronizeCommand extends Command
{
    public static $defaultName = 'app:sync';

    /**
     * @param SynchronizerInterface[] $synchronizers
     */
    public function __construct(
        #[TaggedIterator('app.synchronizer')] private readonly iterable $synchronizers
    ) {
        parent::__construct(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->synchronizers as $synchronizer) {
            $synchronizer->sync();
        }

        return Command::SUCCESS;
    }
}
