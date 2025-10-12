<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Stub;

enum StringBackedEnum: string
{
    case Case1 = 'value1';
    case Case2 = 'value2';
}
