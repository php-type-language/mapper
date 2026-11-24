<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\SkipWhen;

final class CustomFunctionSkipStub
{
    #[SkipWhen('blank(value)')]
    public string $email = '';
}
