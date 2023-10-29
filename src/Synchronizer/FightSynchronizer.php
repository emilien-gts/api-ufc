<?php

namespace App\Synchronizer;

use App\Entity\Event;
use App\Entity\Fight\Fight;
use App\Entity\Fight\FighterFight;
use App\Entity\Fight\Referee;
use App\Entity\Fighter;
use App\Enum\Fight\Corner;
use App\Enum\Fight\Method;
use App\Synchronizer\Base\BaseCrawler;
use App\Synchronizer\Base\BaseSynchronizer;
use App\Synchronizer\Exception\SynchronizerException;
use App\Synchronizer\Helper\SynchronizerHelper;
use App\Synchronizer\Source\FightSource;
use App\Synchronizer\Utils\CrawlerUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 1)]
final class FightSynchronizer extends BaseSynchronizer
{
    public const DETAILS_URL = 'http://ufcstats.com/fight-details';
    protected const BATCH_SIZE = 25;

    /** @var array<string, Fighter> */
    private array $_fighters = [];

    /** @var array<string, Referee> */
    private array $_referees = [];

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        SynchronizerHelper $helper,
        BaseCrawler $crawler,
        private readonly FightSource $source
    ) {
        parent::__construct($logger, $em, $helper, $crawler);
    }

    public function sync(): void
    {
        $this->helper->deleteEntity(FighterFight::class);
        $this->helper->deleteEntity(Fight::class);
        $this->helper->deleteEntity(Referee::class);

        $this->crawler->reset();

        $events = $this->em->getRepository(Event::class)->findAll();
        foreach ($events as $event) {
            $this->syncEventFights($event);
        }
    }

    private function loadData(): void
    {
        // referees
        foreach ($this->_referees as $referee) {
            /** @var Referee $referee */
            $referee = $this->em->getReference(Referee::class, $referee->getId());
            $this->_referees[$referee->getFullName()] = $referee;
        }

        // fighters
        foreach ($this->_fighters as $fighter) {
            /** @var Fighter $fighter */
            $fighter = $this->em->getReference(Fighter::class, $fighter->getId());
            $this->_fighters[$fighter->getFullName()] = $fighter;
        }
    }

    private function syncEventFights(Event $event): void
    {
        $tokens = \array_reverse($this->getTokens($event));
        foreach ($tokens as $token) {
            $this->syncOne($token, $event);
        }

        $this->helper->flushAndClear();
        $this->loadData();

        $this->logger->info(\sprintf('Fights of "%s" are import', $event->getName()));
    }

    protected function getTokens(Event $event): array
    {
        $url = \sprintf('%s/%s', EventSynchronizer::DETAILS_URL, $event->getToken());
        $this->crawler->init($url);

        $domTrs = $this->crawler->getElements($this->source->getTrElementsTokensSelector());

        $tokens = [];
        foreach ($domTrs as $domTr) {
            if ($uri = $domTr->attributes?->getNamedItem('data-link')?->nodeValue) {
                $tokens[] = CrawlerUtils::getTokenFromUri($uri);
            }
        }

        return $tokens;
    }

    private function syncOne(string $token, Event $event): void
    {
        $url = \sprintf('%s/%s', FightSynchronizer::DETAILS_URL, $token);
        $this->crawler->init($url);

        $domData = $this->getDataFromDom();
        $data = $this->transformDomData($domData);

        $fight = new Fight();
        $fight->setToken($token);
        $fight->setEvent($this->em->getReference(Event::class, $event->getId()));
        $fight->setMethod($data['method']);
        $referee = $data['referee'];
        $fight->setReferee($referee);

        foreach ($data['fighters'] as $fighter) {
            $fight->addFighter($fighter);
        }

        $this->em->persist($referee);
        $this->em->persist($fight);
    }

    protected function getDataFromDom(): array
    {
        return [
            'method' => $this->getDataContentFromDom($this->source->getIMethodSelector()),
            'referee' => $this->getDataContentFromDom($this->source->getSpanRefereeSelector()),
            'red_corner' => [
                'fighter' => $this->getDataContentFromDom($this->source->getARedCornerFighterFullName()),
                'status' => $this->getDataContentFromDom($this->source->getIRedCornerFighterStatus()),
            ],
            'blue_corner' => [
                'fighter' => $this->getDataContentFromDom($this->source->getABlueCornerFighterFullName()),
                'status' => $this->getDataContentFromDom($this->source->getIBlueCornerFighterStatus()),
            ],
        ];
    }

    protected function transformDomData(array $domData): array
    {
        return [
            'method' => $this->transformMethod($domData),
            'referee' => $this->transformReferee($domData['referee']),
            'fighters' => [
                0 => $this->transformFighter($domData['red_corner'], Corner::RED),
                1 => $this->transformFighter($domData['blue_corner'], Corner::BLUE),
            ],
        ];
    }

    private function transformMethod(array $domData): Method
    {
        $redCornerStatus = $domData['red_corner']['status'];
        $blueCornerStatus = $domData['blue_corner']['status'];

        if ('D' !== $redCornerStatus && 'NC' !== $redCornerStatus) {
            return Method::tryFromUfcStats($domData['method']);
        }

        $method = $domData['method'];

        if ('NC' === $redCornerStatus && 'NC' === $blueCornerStatus) {
            return Method::NO_CONTEST;
        }

        if ('D' === $redCornerStatus && 'D' === $blueCornerStatus) {
            return match ($method) {
                'Decision - Unanimous' => Method::UNANIMOUS_DRAW,
                'Decision - Split' => Method::SPLIT_DRAW,
                'Decision - Majority' => Method::MAJORITY_DRAW,
                default => Method::OTHER
            };
        }

        throw new SynchronizerException('Unexpected value %s for method', $method);
    }

    private function transformReferee(string $refereeFullName): Referee
    {
        if (isset($this->_referees[$refereeFullName])) {
            return $this->_referees[$refereeFullName];
        }

        $referee = new Referee($refereeFullName);
        $this->_referees[$refereeFullName] = $referee;

        return $referee;
    }

    private function transformFighter(array $data, Corner $corner): FighterFight
    {
        $fullName = (string) $data['fighter'];

        $fighter = $this->_fighters[$fullName] ?? null;
        if (null === $fighter) {
            /** @var Fighter|null $fighter */
            $fighter = $this->em->getRepository(Fighter::class)->findOneBy(['fullName' => $fullName]);
        }

        if (null === $fighter) {
            throw new SynchronizerException(\sprintf('Fighter "%s" not found', $data['fighter']));
        }

        $this->_fighters[$fullName] = $fighter;

        $f = new FighterFight();
        $f->setFighter($fighter);
        $f->setIsWinner('W' === $data['status']);
        $f->setCorner($corner);

        return $f;
    }
}
