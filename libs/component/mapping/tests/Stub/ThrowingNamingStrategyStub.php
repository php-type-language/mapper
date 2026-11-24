<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Naming\NamingStrategyInterface;

final class ThrowingNamingStrategyStub implements NamingStrategyInterface
{
    public function __construct()
    {
        throw new \RuntimeException('boom');
    }

    public function rename(string $name): string
    {
        return $name;
    }
}
