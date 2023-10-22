<?php

namespace App\Synchronizer\Source;

class FightSource implements SynchronizerSource
{
    public function getTrElementsTokensSelector(): string
    {
        return 'tbody.b-fight-details__table-body tr';
    }

    public function getIMethodSelector(): string
    {
        return 'div.b-fight-details div.b-fight-details__fight div.b-fight-details__content > p.b-fight-details__text > i > i:nth-child(2)';
    }

    public function getSpanRefereeSelector(): string
    {
        return 'div.b-fight-details div.b-fight-details__fight div.b-fight-details__content > p.b-fight-details__text > i:nth-child(5) > span';
    }

    public function getARedCornerFighterFullName(): string
    {
        return 'div.b-fight-details__persons > div:nth-child(1) h3.b-fight-details__person-name > a';
    }

    public function getIRedCornerFighterStatus(): string
    {
        return 'div.b-fight-details__persons > div:nth-child(1) i.b-fight-details__person-status';
    }

    public function getABlueCornerFighterFullName(): string
    {
        return 'div.b-fight-details__persons > div:nth-child(2) h3.b-fight-details__person-name > a';
    }

    public function getIBlueCornerFighterStatus(): string
    {
        return 'div.b-fight-details__persons > div:nth-child(2) i.b-fight-details__person-status';
    }
}
