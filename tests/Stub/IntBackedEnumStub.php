<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

enum IntBackedEnumStub: int
{
    case CASE = 0xDEAD_BEEF;
}
