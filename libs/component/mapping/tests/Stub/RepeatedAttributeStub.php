<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapName;

/**
 * Attributes given twice that must not be repeated.
 */
final class RepeatedAttributeStub
{
    #[MapGetter('a')]
    #[MapGetter('b')]
    public string $declared = '';

    #[MapName('a')]
    #[MapName('b')]
    public string $renamed = '';
}
