<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Naming\NamingStrategyInterface;

final class PrefixNamingStrategyStub implements NamingStrategyInterface
{
    public function __construct(
        private readonly string $prefix,
    ) {}

    public function rename(string $name): string
    {
        return $this->prefix . $name;
    }
}
