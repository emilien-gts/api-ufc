<?php

namespace App\Synchronizer\Source;

class EventSource implements SynchronizerSource
{
    public function getAElementsTokensSelector(): string
    {
        return 'tbody > tr > td.b-statistics__table-col > i > a';
    }

    public function getSpanNameSelector(): string
    {
        return 'h2.b-content__title > span.b-content__title-highlight';
    }

    public function getLiDateSelector(): string
    {
        return 'body .b-fight-details ul.b-list__box-list li:nth-child(1)';
    }

    public function getLiLocationSelector(): string
    {
        return 'body .b-fight-details ul.b-list__box-list li:nth-child(2)';
    }
}
