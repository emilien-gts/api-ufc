<?php

namespace App\Api\Transformer;

use App\Api\Resource\FighterApi;
use App\Entity\Fighter;

class ApiFighterTransformer implements ApiTransformerInterface
{
    /**
     * @param Fighter $source
     */
    public function transform($source): FighterApi
    {
        $target = new FighterApi();
        $target->token = $source->getToken();
        $target->fullName = $source->getFullName();
        $target->height = $source->getHeight();
        $target->weight = $source->getWeight();
        $target->reach = $source->getReach();
        $target->stance = $source->getStance()?->value;
        $target->dateOfBirth = $source->getDateOfBirth()?->format('Y/m/d');

        return $target;
    }

    public function supportsTransform(object $source): bool
    {
        return $source instanceof Fighter;
    }
}
