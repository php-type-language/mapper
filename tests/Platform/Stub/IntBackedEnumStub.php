<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Platform\Stub;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Tests\Platform
 */
enum IntBackedEnumStub: int
{
    case ExampleCase = 0xDEAD_BEEF;
}
