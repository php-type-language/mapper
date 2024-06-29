<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Unit\Type\Stub;

enum IntBackedEnum: int
{
    case EXAMPLE = 0xDEAD_BEEF;
}
