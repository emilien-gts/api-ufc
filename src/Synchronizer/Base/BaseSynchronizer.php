<?php

namespace App\Synchronizer\Base;

use App\Synchronizer\Helper\SynchronizerHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.synchronizer')]
abstract class BaseSynchronizer
{
    protected const BATCH_SIZE = 250;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly EntityManagerInterface $em,
        protected readonly SynchronizerHelper $helper,
        protected readonly BaseCrawler $crawler,
    ) {
    }

    abstract public function sync(): void;

    /**
     * @return array<string>
     */
    abstract protected function getTokens(): array;

    /**
     * @return array<string>
     */
    abstract protected function getDataFromDom(): array;

    /**
     * @param array<string> $domData
     *
     * @return array<string, mixed>
     */
    abstract protected function transformDomData(array $domData): array;
}
