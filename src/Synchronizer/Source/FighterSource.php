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
}
