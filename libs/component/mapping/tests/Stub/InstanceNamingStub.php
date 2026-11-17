<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;
use TypeLang\Mapper\Naming\KebabCaseNamingStrategy;

#[NamingStrategy(new KebabCaseNamingStrategy())]
final class InstanceNamingStub
{
    public string $firstName;
}
