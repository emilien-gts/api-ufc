<?php

namespace App\Api\Transformer;

use App\Api\Exception\ApiException;
use App\Api\Resource\ApiResourceInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ApiTransformerService
{
    /**
     * @var ApiTransformerInterface[]
     */
    private iterable $transformers;

    /**
     * @param ApiTransformerInterface[] $transformers
     */
    public function __construct(#[TaggedIterator('app.api.transformer')] iterable $transformers)
    {
        $this->transformers = $transformers;
    }

    /**
     * @throws ApiException
     */
    public function transform(object $entity): ApiResourceInterface
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supportsTransform($entity)) {
                return $transformer->transform($entity);
            }
        }

        throw new ApiException('No transformer found');
    }
}
