<?php

namespace App\Api\State;

use ApiPlatform\Doctrine\Orm\Paginator;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use App\Api\Exception\ApiException;
use App\Api\Resource\ApiResourceInterface;
use App\Entity\Fighter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntityStateProvider extends AbstractStateProvider
{
    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     *
     * @throws ApiException
     * @throws \Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator|ApiResourceInterface
    {
        if ($operation instanceof CollectionOperationInterface) {
            return $this->provideCollection($operation, $uriVariables, $context);
        }

        return $this->provideItem($operation, $uriVariables, $context);
    }

    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     *
     * @throws \Exception
     */
    private function provideCollection(Operation $operation, array $uriVariables = [], array $context = []): TraversablePaginator
    {
        $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);
        \assert($entities instanceof Paginator);

        $dtos = [];
        /** @var Fighter $entity */
        foreach ($entities as $entity) {
            $dto = $this->transformerService->transform($entity);
            $dtos[] = $dto;
        }

        return new TraversablePaginator(
            new \ArrayIterator($dtos),
            $entities->getCurrentPage(),
            $entities->getItemsPerPage(),
            $entities->getTotalItems()
        );
    }

    /**
     * @param array<string, mixed>                                                   $uriVariables
     * @param array<string, mixed>|array{request?: Request, resource_class?: string} $context
     *
     * @throws ApiException
     * @throws \Exception
     */
    private function provideItem(Operation $operation, array $uriVariables = [], array $context = []): ApiResourceInterface
    {
        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);
        if (null === $entity) {
            throw new ApiException('Entity not found', Response::HTTP_NOT_FOUND);
        }

        \assert($entity instanceof Fighter);

        return $this->transformerService->transform($entity);
    }
}
