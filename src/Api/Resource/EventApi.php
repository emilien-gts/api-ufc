<?php

namespace App\Api\Resource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Api\State\EntityStateProvider;
use App\Entity\Event;

#[ApiResource(
    shortName: 'Event',
    collectDenormalizationErrors: true,
    provider: EntityStateProvider::class,
    stateOptions: new Options(entityClass: Event::class)
)]
class EventApi implements ApiResourceInterface
{
    #[ApiProperty(identifier: true)]
    public string $token;
    public ?string $name = null;
    public ?string $date = null;
    public ?bool $isPpv = null;
    public ?bool $isUltimeFighter = null;
    #[ApiProperty(readableLink: true)]
    public ?LocationApi $location = null;
}
