<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;

#[NamingStrategy(ThrowingNamingStrategyStub::class)]
final class ThrowingNamingStub
{
    public string $firstName;
}
