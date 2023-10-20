<?php

namespace App\Synchronizer\Source;

class FighterSource
{
    public function getAElementsTokensSelector(): string
    {
        return 'tbody > tr > td.b-statistics__table-col:nth-child(2) > a';
    }

    public function getSpanFullnameSelector(): string
    {
        return 'h2.b-content__title > span.b-content__title-highlight';
    }

    public function getLiHeightSelector(): string
    {
        return 'div.b-list__info-box.b-list__info-box_style_small-width.js-guide > ul > li:nth-child(1)';
    }

    public function getLiWeightSelector(): string
    {
        return 'div.b-list__info-box.b-list__info-box_style_small-width.js-guide > ul > li:nth-child(2)';
    }

    public function getLiReachSelector(): string
    {
        return 'div.b-list__info-box.b-list__info-box_style_small-width.js-guide > ul > li:nth-child(3)';
    }

    public function getLiStanceSelector(): string
    {
        return 'div.b-list__info-box.b-list__info-box_style_small-width.js-guide > ul > li:nth-child(4)';
    }

    public function getLiDateOfBirthSelector(): string
    {
        return 'div.b-list__info-box.b-list__info-box_style_small-width.js-guide > ul > li:nth-child(5)';
    }
}
