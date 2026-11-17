<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\NamingStrategy;

#[NamingStrategy('snake_case')]
final class NamingStub
{
    public string $firstName;

    #[MapName('ID')]
    public int $userId;
}
