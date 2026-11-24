<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

/**
 * Expression reading a private property of the object.
 */
final class ErrorSkipStub
{
    #[SkipWhen('this.secret')]
    public string $name = 'John';

    // @phpstan-ignore-next-line the property is read by the expression only
    private bool $secret = true;
}
