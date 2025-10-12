<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Stub;

enum IntBackedEnum: int
{
    case Case1 = 1;
    case Case2 = 2;
}
