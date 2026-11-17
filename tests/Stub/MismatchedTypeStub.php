<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;

/**
 * Type of the mapping differs from the native type of the property.
 */
final class MismatchedTypeStub
{
    #[MapType('string')]
    public int $value;
}
