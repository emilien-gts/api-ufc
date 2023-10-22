<?php

namespace App\Synchronizer\Base;

use App\Synchronizer\Exception\SynchronizerException;
use App\Synchronizer\Helper\SynchronizerHelper;
use App\Synchronizer\Utils\CrawlerUtils;
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

    public function getDataContentFromDom(string $selector): string
    {
        $domElement = $this->crawler->getDomElement($selector);
        if (null === $domElement) {
            throw new SynchronizerException(\sprintf('Data not found because of selector %s', $selector));
        }

        $data = CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($domElement->childNodes->getIterator());
        if (empty($data)) {
            throw new SynchronizerException(\sprintf('Data not found (selector %s)', $selector));
        }

        return \trim($data);
    }
}
