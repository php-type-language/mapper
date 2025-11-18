<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\ObjectType\ObjectFromArrayType;
use TypeLang\Mapper\Type\ObjectType\ObjectToArrayType;

/**
 * @template TResult of object|array = object|array<array-key, mixed>
 *
 * @template-extends AsymmetricType<TResult, object>
 */
final class ObjectType extends AsymmetricType
{
    public function __construct()
    {
        parent::__construct(
            normalize: new ObjectToArrayType(),
            denormalize: new ObjectFromArrayType(),
        );
    }
}
