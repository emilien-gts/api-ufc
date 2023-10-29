<?php

namespace App\Synchronizer;

use App\Entity\Fighter;
use App\Enum\Stance;
use App\Model\FeetAndInchesToCentimeters;
use App\Model\LbsToKilograms;
use App\Synchronizer\Base\BaseCrawler;
use App\Synchronizer\Base\BaseSynchronizer;
use App\Synchronizer\Exception\SynchronizerException;
use App\Synchronizer\Helper\SynchronizerHelper;
use App\Synchronizer\Source\FighterSource;
use App\Synchronizer\Utils\CrawlerUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 10)]
final class FighterSynchronizer extends BaseSynchronizer
{
    public const LIST_URL = 'http://ufcstats.com/statistics/fighters';
    public const DETAILS_URL = 'http://ufcstats.com/fighter-details/';

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SynchronizerHelper $helper,
        BaseCrawler $crawler,
        private readonly FighterSource $source
    ) {
        parent::__construct($logger, $em, $helper, $crawler);
    }

    public function sync(): void
    {
        $this->helper->deleteEntity(Fighter::class);
        $this->crawler->reset();

        $tokens = $this->getTokens();
        $nbTokens = \count($tokens);
        foreach ($tokens as $key => $token) {
            $this->syncOne($token);

            if (0 === $key % self::BATCH_SIZE) {
                $this->logger->info(\sprintf('%s / %s fighters import', $key, $nbTokens));
                $this->helper->flushAndClear();
            }
        }

        $this->logger->info(\sprintf('%s / %s fighters import', $nbTokens, $nbTokens));
        $this->helper->flushAndClear();
    }

    protected function getTokens(): array
    {
        $tokens = [];
        foreach (\range('a', 'z') as $letter) {
            $url = \sprintf('%s?char=%s&page=all', FighterSynchronizer::LIST_URL, $letter);
            $tokensFromCurrentPage = $this->getTokensFromCurrentPage($url);
            $tokens = \array_merge($tokens, $tokensFromCurrentPage);
        }

        return $tokens;
    }

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

    private function syncOne(string $token): void
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

    protected function getDataFromDom(): array
    {
        return [
            'full_name' => $this->getFullNameFromDom(),
            'height' => $this->getTextContentFromLi($this->source->getLiHeightSelector()),
            'weight' => $this->getTextContentFromLi($this->source->getLiWeightSelector()),
            'reach' => $this->getTextContentFromLi($this->source->getLiReachSelector()),
            'stance' => $this->getTextContentFromLi($this->source->getLiStanceSelector()),
            'dateOfBirth' => $this->getTextContentFromLi($this->source->getLiDateOfBirthSelector()),
        ];
    }

    private function getFullNameFromDom(): string
    {
        $domElement = $this->crawler->getDomElement($this->source->getSpanFullnameSelector());
        if (!isset($domElement->textContent)) {
            throw new SynchronizerException('Name of fighter not found.');
        }

        return \trim($domElement->textContent);
    }

    private function getTextContentFromLi(string $selector): ?string
    {
        $domElement = $this->crawler->getDomElement($selector);
        if (null === $domElement) {
            return null;
        }

        return CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($domElement->childNodes->getIterator());
    }

    protected function transformDomData(array $domData): array
    {
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
            $data['stance'] = Stance::tryFrom(strtolower($domData['stance']));
        }

        if (isset($domData['dateOfBirth'])) {
            $data['dateOfBirth'] = $this->helper->createDatetimeFromUfcFormat($domData['dateOfBirth']);
        }

        return \array_merge([
            'height' => null,
            'weight' => null,
            'reach' => null,
            'stance' => null,
            'dateOfBirth' => null,
        ], $data);
    }
}
