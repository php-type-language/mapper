<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\Direction;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapOnly;
use TypeLang\Mapper\Mapping\MapSetter;

final class AccessorStub
{
    public string $name = 'default';

    #[MapOnly(Direction::Normalization)]
    public string $createdAt = 'today';

    #[MapGetter('name')]
    public function getName(): string
    {
        return \strtoupper($this->name);
    }

    #[MapSetter('name')]
    public function setName(string $name): void
    {
        $this->name = 'set:' . $name;
    }
}
