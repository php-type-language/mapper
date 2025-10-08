<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\NormalizeAsArray;
use TypeLang\Mapper\Mapping\OnTypeError;
use TypeLang\Mapper\Mapping\SkipWhenNull;

#[NormalizeAsArray]
#[OnTypeError('Class error')]
final class CombinedAttributesClass
{
    #[MapType('int')]
    #[MapName('id_field')]
    #[OnTypeError('Property error')]
    public string $id;

    #[SkipWhenNull]
    public ?string $optional;
}
