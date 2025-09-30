<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeInterface<mixed>
 */
class MixedType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return true;
    }

    public function cast(mixed $value, Context $context): mixed
    {
        $type = $context->getTypeByValue($value);

        return $type->cast($value, $context);
    }
}
