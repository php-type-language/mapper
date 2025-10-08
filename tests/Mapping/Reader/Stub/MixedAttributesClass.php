<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\SkipWhenNull;

final class MixedAttributesClass
{
    #[MapType('int')]
    public string $typed;

    #[MapName('renamed')]
    public string $aliased;

    #[SkipWhenNull]
    public ?string $nullable;

    public string $plain;
}
