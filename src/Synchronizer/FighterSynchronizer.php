<?php

namespace App\Synchronizer;

use App\Entity\Fighter;
use App\Enum\Stance;
use App\Model\FeetAndInchesToCentimeters;
use App\Model\LbsToKilograms;
use App\Synchronizer\Base\BaseCrawler;
use App\Synchronizer\Exception\SynchronizerException;
use App\Synchronizer\Helper\SynchronizerHelper;
use App\Synchronizer\Model\SynchronizerInterface;
use App\Synchronizer\Source\FighterSource;
use App\Synchronizer\Utils\CrawlerUtils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class FighterSynchronizer implements SynchronizerInterface
{
    public const LIST_URL = 'http://ufcstats.com/statistics/fighters';
    public const DETAILS_URL = 'http://ufcstats.com/fighter-details/';

    private const BATCH_SIZE = 25;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $em,
        private readonly SynchronizerHelper $helper,
        private readonly BaseCrawler $crawler,
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
        $nbTokens = \count($tokens);
        foreach ($tokens as $key => $token) {
            $this->createFighter($token);

            if (0 !== $key && 0 === $key % self::BATCH_SIZE) {
                $this->logger->info(\sprintf('%s / %s fighters import', $key, $nbTokens));
                $this->helper->flushAndClear();
            }
        }

        $this->logger->info(\sprintf('%s / %s fighters import', $nbTokens, $nbTokens));
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
            $tokens[] = CrawlerUtils::getTokenFromDomLink($domLink);
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
        $data = $this->transformDomData($domData);

        $f = new Fighter($token);
        $f->setFullName($data['full_name']);
        $f->setHeight($data['height']);
        $f->setWeight($data['weight']);
        $f->setReach($data['reach']);
        $f->setStance($data['stance']);
        $f->setDateOfBirth($data['dateOfBirth']);

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

        $heightDomElement = $this->crawler->getDomElement($this->source->getLiHeightSelector());
        $weightDomElement = $this->crawler->getDomElement($this->source->getLiWeightSelector());
        $reachDomElement = $this->crawler->getDomElement($this->source->getLiReachSelector());
        $stanceDomElement = $this->crawler->getDomElement($this->source->getLiStanceSelector());
        $dateOfBirthDomElement = $this->crawler->getDomElement($this->source->getLiDateOfBirthSelector());

        return [
            'full_name' => \trim($fullNameDomElement->textContent),
            'height' => $heightDomElement ? CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($heightDomElement->childNodes->getIterator()) : null,
            'weight' => $weightDomElement ? CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($weightDomElement->childNodes->getIterator()) : null,
            'reach' => $reachDomElement ? CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($reachDomElement->childNodes->getIterator()) : null,
            'stance' => $stanceDomElement ? CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($stanceDomElement->childNodes->getIterator()) : null,
            'dateOfBirth' => $dateOfBirthDomElement ? CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($dateOfBirthDomElement->childNodes->getIterator()) : null,
        ];
    }

    /**
     * @param array<string, string> $domData
     *
     * @return array{
     *      full_name: string,
     *      height: int|null,
     *      weight: int|null,
     *      reach: int|null,
     *      stance: Stance|null,
     *      dateOfBirth: DateTimeImmutable|null
     *  }
     */
    private function transformDomData(array $domData): array
    {
        $data = ['height' => null, 'weight' => null, 'reach' => null, 'stance' => null, 'dateOfBirth' => null];
        $data['full_name'] = $domData['full_name'];

        if (isset($domData['height'])) {
            $data['height'] = (new FeetAndInchesToCentimeters())->transform($domData['height']);
        }

        if (isset($domData['weight'])) {
            $weight = \str_replace(' lbs.', '', $domData['weight']);
            $data['weight'] = (new LbsToKilograms())->transform($weight);
        }

        if (isset($domData['reach'])) {
            $data['reach'] = (new FeetAndInchesToCentimeters())->transform($domData['reach']);
        }

        if (isset($domData['stance'])) {
            $data['stance'] = Stance::tryFrom((string) \strtolower($domData['stance']));
        }

        $dateOfBirthPattern = '/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s\d{1,2},\s\d{4}$/';
        if (isset($domData['dateOfBirth']) && 1 === preg_match($dateOfBirthPattern, $domData['dateOfBirth'])) {
            $data['dateOfBirth'] = \DateTimeImmutable::createFromFormat('M d, Y', $domData['dateOfBirth']) ?: null;
        }

        return $data;
    }
}
