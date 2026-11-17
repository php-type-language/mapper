<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;

#[NamingStrategy('Snake_Case')]
final class BrokenNamingStub
{
    public string $firstName;
}
