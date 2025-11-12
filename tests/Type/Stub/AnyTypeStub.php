<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Stub;

use TypeLang\Mapper\Context\MappingContext;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Tests\Type
 */
final class AnyTypeStub implements TypeInterface
{
    public function match(mixed $value, MappingContext $context): bool
    {
        return true;
    }

    public function cast(mixed $value, MappingContext $context): mixed
    {
        return $value;
    }
}
