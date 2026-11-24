<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\NamingStrategy;

#[NamingStrategy('kebab-case')]
final class KebabChildStub extends NamedParentStub
{
    public string $lastName = '';
}
