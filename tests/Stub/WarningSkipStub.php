<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

/**
 * Expression reading a property the object does not have.
 */
final class WarningSkipStub
{
    #[SkipWhen('this.missing')]
    public string $name = 'John';
}
