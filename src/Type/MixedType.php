<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\MappingContext;

/**
 * @template-implements TypeInterface<mixed>
 */
class MixedType implements TypeInterface
{
    public function match(mixed $value, MappingContext $context): bool
    {
        return true;
    }

    public function cast(mixed $value, MappingContext $context): mixed
    {
        $type = $context->getTypeByValue($value);

        return $type->cast($value, $context);
    }
}
