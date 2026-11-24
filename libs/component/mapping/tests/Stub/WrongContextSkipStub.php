<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

/**
 * Names of the object of the expression that are not allowed.
 */
final class WrongContextSkipStub
{
    #[SkipWhen('value === ""', context: 'value')]
    public string $reserved = '';

    #[SkipWhen('self.name === ""', context: 'my object')]
    public string $spaced = '';
}
