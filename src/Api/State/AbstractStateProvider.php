<?php

namespace App\Api\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\State\ProviderInterface;
use App\Api\Transformer\ApiTransformerService;

abstract class AbstractStateProvider implements ProviderInterface
{
    public function __construct(
        protected readonly ApiTransformerService $transformerService,
        protected readonly CollectionProvider $collectionProvider,
        protected readonly ItemProvider $itemProvider,
    ) {
    }
}
