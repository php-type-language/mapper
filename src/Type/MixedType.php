<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;

class MixedType implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return true;
    }

    /**
     * @throws TypeNotFoundException
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
     */
    public function cast(mixed $value, Context $context): mixed
    {
        return $context->getTypes()
            ->getByValue($value)
            ->cast($value, $context);
    }
}
