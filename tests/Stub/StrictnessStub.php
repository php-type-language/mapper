<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;

/**
 * Properties with their own strictness over the context.
 */
final class StrictnessStub
{
    #[MapType('int', strict: true)]
    public int $strict;

    #[MapType('int', strict: false)]
    public int $loose;
}
