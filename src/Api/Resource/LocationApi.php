<?php

namespace App\Api\Resource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Api\State\EntityStateProvider;
use App\Entity\Location;

#[ApiResource(
    shortName: 'Location',
    operations: [
        new GetCollection(uriTemplate: 'locations'),
        new Get(uriTemplate: 'locations/{city}'),
    ],
    collectDenormalizationErrors: true,
    provider: EntityStateProvider::class,
    stateOptions: new Options(entityClass: Location::class)
)]
class LocationApi implements ApiResourceInterface
{
    #[ApiProperty(identifier: true)]
    public string $city;
    public ?string $region = null;
    public ?string $country = null;
}
