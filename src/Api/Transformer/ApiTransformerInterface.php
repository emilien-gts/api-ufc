<?php

namespace App\Api\Transformer;

use App\Api\Resource\ApiResourceInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.api.transformer')]
interface ApiTransformerInterface
{
    public function transform(object $source): ApiResourceInterface;

    public function supportsTransform(object $source): bool;
}
