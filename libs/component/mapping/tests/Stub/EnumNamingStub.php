<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;

#[NamingStrategy(NamingCaseStub::Upper)]
final class EnumNamingStub
{
    public string $firstName;
}
