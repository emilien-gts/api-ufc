<?php

namespace App\Api\Resource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Api\State\EntityStateProvider;
use App\Entity\Fighter;

#[ApiResource(
    shortName: 'Fighter',
    collectDenormalizationErrors: true,
    provider: EntityStateProvider::class,
    stateOptions: new Options(entityClass: Fighter::class)
)]
class FighterApi implements ApiResourceInterface
{
    #[ApiProperty(identifier: true)]
    public string $token;
    public ?string $fullName = null;
    public ?int $height = null;
    public ?int $weight = null;
    public ?int $reach = null;
    public ?string $stance = null;
    public ?string $dateOfBirth = null;
}
