<?php

namespace App\Synchronizer;

use App\Entity\Fighter;
use App\Synchronizer\Base\BaseCrawler;
use App\Synchronizer\Exception\SynchronizerException;
use App\Synchronizer\Helper\CrawlerHelper;
use App\Synchronizer\Helper\SynchronizerHelper;
use App\Synchronizer\Model\SynchronizerInterface;
use App\Synchronizer\Source\FighterSource;
use Doctrine\ORM\EntityManagerInterface;

class FighterSynchronizer implements SynchronizerInterface
{
    public const LIST_URL = 'http://ufcstats.com/statistics/fighters';
    public const DETAILS_URL = 'http://ufcstats.com/fighter-details/';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SynchronizerHelper $helper,
        private readonly BaseCrawler $crawler,
        private readonly CrawlerHelper $crawlerHelper,
        private readonly FighterSource $source
    ) {
    }

    /**
     * @throws SynchronizerException
     * @throws \Exception
     */
    public function sync(): void
    {
        $this->helper->deleteEntity(Fighter::class);
        $this->crawler->reset();

        $tokens = $this->getTokens();
        foreach ($tokens as $token) {
            $this->createFighter($token);
        }

        $this->helper->flushAndClear();
    }

    /**
     * @return array<string>
     *
     * @throws \Exception
     */
    private function getTokens(): array
    {
        $tokens = [];
        foreach (\range('a', 'z') as $letter) {
            $url = \sprintf('%s?char=%s&page=all', FighterSynchronizer::LIST_URL, $letter);
            $tokensFromCurrentPage = $this->getTokensFromCurrentPage($url);
            $tokens = \array_merge($tokens, $tokensFromCurrentPage);
        }

        return $tokens;
    }

    /**
     * @return array<string>
     *
     * @throws \Exception
     */
    private function getTokensFromCurrentPage(string $url): array
    {
        $this->crawler->init($url);
        $domLinks = $this->crawler->getLinks($this->source->getAElementsTokensSelector());

        $tokens = [];
        foreach ($domLinks as $domLink) {
            $tokens[] = $this->crawlerHelper->getTokenFromDomLink($domLink);
        }

        return $tokens;
    }

    /**
     * @throws SynchronizerException
     */
    private function createFighter(string $token): void
    {
        $url = \sprintf('%s/%s', FighterSynchronizer::DETAILS_URL, $token);
        $this->crawler->init($url);

        $domData = $this->getDataFromDom();

        $f = new Fighter($token);
        $f->setFullName($domData['full_name']);

        $this->em->persist($f);
    }

    /**
     * @return array{
     *     full_name: string
     * }
     *
     * @throws SynchronizerException
     */
    private function getDataFromDom(): array
    {
        $fullNameDomElement = $this->crawler->getDomElement($this->source->getSpanFullnameSelector());
        if (!isset($fullNameDomElement->textContent)) {
            throw new SynchronizerException('Le nom du combattant n\'a pas été trouvé');
        }

        return [
            'full_name' => \trim($fullNameDomElement->textContent),
        ];
    }
}
