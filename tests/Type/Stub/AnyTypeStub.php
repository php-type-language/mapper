<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Stub;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Tests\Type
 */
final class AnyTypeStub implements TypeInterface
{
    public function match(mixed $value, Context $context): bool
    {
        return true;
    }

    public function cast(mixed $value, Context $context): mixed
    {
        return $value;
    }
}
