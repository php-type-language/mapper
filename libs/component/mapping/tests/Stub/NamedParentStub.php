<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;

#[NamingStrategy('snake_case')]
class NamedParentStub
{
    public string $firstName = '';
}
