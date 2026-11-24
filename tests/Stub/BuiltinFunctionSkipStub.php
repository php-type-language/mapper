<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

final class BuiltinFunctionSkipStub
{
    #[SkipWhen('max(1, 2) > 1 and value === ""')]
    public string $email = '';

    public string $name = 'John';
}
