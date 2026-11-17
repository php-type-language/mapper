<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Naming\NamingStrategyInterface;

enum NamingCaseStub implements NamingStrategyInterface
{
    case Upper;

    public function rename(string $name): string
    {
        return \strtoupper($name);
    }
}
