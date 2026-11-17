<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;
use TypeLang\Mapper\Naming\PascalCaseNamingStrategy;

#[NamingStrategy(PascalCaseNamingStrategy::class)]
final class ClassNamingStub
{
    public string $firstName;
}
