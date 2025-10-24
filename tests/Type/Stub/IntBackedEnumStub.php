<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Stub;

enum IntBackedEnumStub: int
{
    case ExampleCase = 0xDEAD_BEEF;
}
