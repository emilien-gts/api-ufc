<?php

namespace App\Api\Transformer;

use App\Api\Resource\EventApi;
use App\Entity\Event;

class ApiEventTransformer implements ApiTransformerInterface
{
    public function __construct(private readonly ApiLocationTransformer $locationTransformer)
    {
    }

    /**
     * @param Event $source
     */
    public function transform($source): EventApi
    {
        $target = new EventApi();
        $target->token = $source->getToken();
        $target->name = $source->getName();
        $target->date = $source->getDate()?->format('Y-m-d');
        $target->isPpv = $source->isPpv();
        $target->isUltimeFighter = $source->isUltimeFighter();
        $target->location = $source->getLocation()
            ? $this->locationTransformer->transform($source->getLocation())
            : null;

        return $target;
    }

    public function supportsTransform(object $source): bool
    {
        return $source instanceof Event;
    }
}
