<?php

namespace App\Synchronizer;

use App\Entity\Event;
use App\Entity\Location;
use App\Synchronizer\Base\BaseCrawler;
use App\Synchronizer\Base\BaseSynchronizer;
use App\Synchronizer\Exception\SynchronizerException;
use App\Synchronizer\Helper\SynchronizerHelper;
use App\Synchronizer\Source\EventSource;
use App\Synchronizer\Utils\CrawlerUtils;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 5)]
final class EventSynchronizer extends BaseSynchronizer
{
    public const LIST_URL = 'http://ufcstats.com/statistics/events/completed';
    public const DETAILS_URL = 'http://ufcstats.com/event-details/';

    private const UFC_1_TOKEN = '6420efac0578988b'; // not in list

    /** @var array<string, Location> */
    private array $_locations;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SynchronizerHelper $helper,
        BaseCrawler $crawler,
        private readonly EventSource $source
    ) {
        parent::__construct($logger, $em, $helper, $crawler);

        $this->_locations = [];
    }

    /**
     * @throws SynchronizerException
     * @throws \Exception
     */
    public function sync(): void
    {
        $this->helper->deleteEntity(Event::class);
        $this->helper->deleteEntity(Location::class);

        $this->crawler->reset();

        $tokens = \array_reverse($this->getTokens());
        $nbTokens = \count($tokens);

        foreach ($tokens as $key => $token) {
            $this->syncOne($token);

            if (0 === $key % self::BATCH_SIZE) {
                $this->logger->info(\sprintf('%s / %s events import', $key, $nbTokens));
                $this->helper->flushAndClear();

                $this->refreshEmData();
            }
        }

        $this->logger->info(\sprintf('%s / %s events import', $nbTokens, $nbTokens));
        $this->helper->flushAndClear();
    }

    private function refreshEmData(): void
    {
        // locations
        foreach ($this->_locations as $location) {
            $key = \sprintf('%s', $location->getCity());
            /** @var Location $location */
            $location = $this->em->getRepository(Location::class)->find($location->getId());
            $this->_locations[$key] = $location;
        }
    }

    /**
     * @return array<string>
     *
     * @throws \Exception
     */
    protected function getTokens(): array
    {
        $url = \sprintf('%s?page=all', EventSynchronizer::LIST_URL);
        $this->crawler->init($url);

        $domLinks = $this->crawler->getLinks($this->source->getAElementsTokensSelector());

        $tokens = [];
        foreach ($domLinks as $domLink) {
            $tokens[] = CrawlerUtils::getTokenFromDomLink($domLink);
        }

        $tokens[] = self::UFC_1_TOKEN;

        return $tokens;
    }

    /**
     * @throws SynchronizerException
     */
    private function syncOne(string $token): void
    {
        $url = \sprintf('%s/%s', EventSynchronizer::DETAILS_URL, $token);
        $this->crawler->init($url);

        $domData = $this->getDataFromDom();
        $data = $this->transformDomData($domData);

        $event = new Event($token);
        $event->setName($data['name']);
        $event->setDate($data['date']);
        $event->setIsPpv($data['is_ppv']);
        $event->setIsUltimeFighter($data['is_ultimate_fighter']);
        $event->setLocation($data['location']);

        $this->em->persist($event);
    }

    /**
     * @return array<string, string>
     *
     * @throws SynchronizerException
     */
    protected function getDataFromDom(): array
    {
        return [
            'name' => $this->getNameFromDom(),
            'date' => $this->getDateFromDom(),
            'location' => $this->getLocationFromDom(),
        ];
    }

    private function getNameFromDom(): string
    {
        $nameDomElement = $this->crawler->getDomElement($this->source->getSpanNameSelector());
        if (!isset($nameDomElement->textContent)) {
            throw new SynchronizerException('Name of event not found');
        }

        return \trim($nameDomElement->textContent);
    }

    private function getDateFromDom(): string
    {
        $domElement = $this->crawler->getDomElement($this->source->getLiDateSelector());
        if (null === $domElement) {
            throw new SynchronizerException('Date of event not found because of selector');
        }

        $data = CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($domElement->childNodes->getIterator());

        if (empty($data)) {
            throw new SynchronizerException('Date of event not found');
        }

        return $data;
    }

    private function getLocationFromDom(): string
    {
        $domElement = $this->crawler->getDomElement($this->source->getLiLocationSelector());
        if (null === $domElement) {
            throw new SynchronizerException('Location of event not found because of selector');
        }

        $data = CrawlerUtils::getFirstNotEmptyDomTextContentFromIterable($domElement->childNodes->getIterator());

        if (empty($data)) {
            throw new SynchronizerException('Location of event not found');
        }

        return $data;
    }

    /**
     * @param array<string, string> $domData
     *
     * @return array{
     *      name: string,
     *      date: DateTimeImmutable,
     *      is_ppv: bool,
     *      is_ultimate_fighter: bool,
     *      location: Location
     *  }
     */
    protected function transformDomData(array $domData): array
    {
        /** @var \DateTimeImmutable $date */
        $date = $this->helper->createDatetimeFromUfcFormat($domData['date']);

        $data['name'] = $domData['name'];
        $data['date'] = $date;
        $data['is_ppv'] = $this->isPpv($domData['name']);
        $data['is_ultimate_fighter'] = $this->isUltimateFighter($domData['name']);
        $data['location'] = $this->findOrCreateLocation($domData['location']);

        return $data;
    }

    private function isPpv(string $eventName): bool
    {
        $pattern = '/^UFC \d+: .*/';

        return (bool) \preg_match($pattern, $eventName);
    }

    private function isUltimateFighter(string $eventName): bool
    {
        return \str_contains($eventName, 'The Ultimate Fighter');
    }

    private function findOrCreateLocation(string $location): Location
    {
        $locationAsArray = \explode(', ', $location);
        $city = $locationAsArray[0];
        if (2 === \count($locationAsArray)) {
            $region = null;
            $country = $locationAsArray[1];
        } else {
            $region = $locationAsArray[1];
            $country = $locationAsArray[2] ?? null;
        }

        $key = \sprintf('%s', $city);

        if (isset($this->_locations[$key])) {
            return $this->_locations[$key];
        }

        $location = new Location($city, $region, $country);
        $this->_locations[$key] = $location;

        return $location;
    }
}
