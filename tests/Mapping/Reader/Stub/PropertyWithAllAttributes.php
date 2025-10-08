<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\OnUndefinedError;
use TypeLang\Mapper\Mapping\SkipWhenNull;

final class PropertyWithAllAttributes
{
    #[MapType('string')]
    #[MapName('aliased')]
    #[OnTypeError('Invalid type')]
    #[OnUndefinedError('Missing field')]
    #[SkipWhenNull]
    public mixed $complex;
}
