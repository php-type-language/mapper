<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

final class CustomFunctionSkipStub
{
    #[SkipWhen('blank(value)')]
    public string $email = '';

    #[SkipWhen('blank(owner.name)', context: 'owner')]
    public string $name = 'John';
}
