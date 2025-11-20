<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Type\ObjectType\ObjectFromArrayType;
use TypeLang\Mapper\Type\ObjectType\ObjectToArrayType;

/**
 * @template-extends AsymmetricType<object|array<array-key, mixed>, object>
 */
final class ObjectType extends AsymmetricType
{
    /**
     * @param TypeInterface<array<array-key, mixed>, array<array-key, mixed>> $input
     */
    public function __construct(TypeInterface $input = new ArrayType())
    {
        parent::__construct(
            normalize: new ObjectToArrayType(),
            denormalize: new ObjectFromArrayType(
                input: $input,
            ),
        );
    }
}
