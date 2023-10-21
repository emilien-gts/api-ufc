<?php

namespace App\Api\State;

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\Doctrine\Orm\State\ItemProvider;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Api\Resource\ApiResourceInterface;
use App\Api\Service\ApiTransformerService;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractStateProvider implements ProviderInterface
{
    public function __construct(
        protected readonly ApiTransformerService $transformerService,
        protected readonly CollectionProvider $collectionProvider,
        protected readonly ItemProvider $itemProvider,
    ) {
    }

    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     */
    abstract protected function provideCollection(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator;

    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     */
    abstract protected function provideItem(Operation $operation, array $uriVariables = [], array $context = []): ApiResourceInterface;
}
