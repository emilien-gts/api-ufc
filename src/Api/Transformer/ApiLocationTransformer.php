<?php

namespace App\Api\Transformer;

use App\Api\Resource\LocationApi;
use App\Entity\Location;

class ApiLocationTransformer implements ApiTransformerInterface
{
    /**
     * @param Location $source
     */
    public function transform($source): LocationApi
    {
        $target = new LocationApi();
        $target->city = $source->getCity();
        $target->region = $source->getRegion();
        $target->country = $source->getCountry();

        return $target;
    }

    public function supportsTransform(object $source): bool
    {
        return $source instanceof Location;
    }
}
