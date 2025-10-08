<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;
use TypeLang\Mapper\Mapping\SkipWhenEmpty;
use TypeLang\Mapper\Mapping\SkipWhenNull;

final class PropertyWithMultipleSkipConditions
{
    #[SkipWhenNull]
    #[SkipWhenEmpty]
    #[SkipWhen('value === 0')]
    #[SkipWhen('value < 0', context: 'negative')]
    public ?int $value;
}
