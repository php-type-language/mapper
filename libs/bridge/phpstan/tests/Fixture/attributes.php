<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan\Tests\Fixture;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;

final class AttributesFixture
{
    #[MapType('list<int>')]
    public array $valid = [];

    #[MapType('list<int')]
    public array $invalid = [];

    #[MapType(strict: true, type: 'array{')]
    public array $named = [];

    #[MapName('list<int')]
    public array $other = [];
}
