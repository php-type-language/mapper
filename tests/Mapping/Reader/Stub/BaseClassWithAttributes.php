<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\NormalizeAsArray;

#[NormalizeAsArray]
abstract class BaseClassWithAttributes
{
    #[MapType('int')]
    public string $baseField;
}
