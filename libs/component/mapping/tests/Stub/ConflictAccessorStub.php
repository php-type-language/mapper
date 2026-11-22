<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\Direction;
use TypeLang\Mapper\Mapping\MapOnly;
use TypeLang\Mapper\Mapping\MapSetter;

final class ConflictAccessorStub
{
    #[MapOnly(Direction::Normalization)]
    public string $name = '';

    #[MapSetter('name')]
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
