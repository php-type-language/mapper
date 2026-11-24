<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Field declared by a getter and a setter over a private property, and a
 * property that is only read.
 */
final class AccessorStub
{
    private string $name = 'default';

    #[MapGetter]
    public string $createdAt = 'today';

    public function getStoredName(): string
    {
        return $this->name;
    }

    #[MapGetter('name')]
    public function getName(): string
    {
        return \strtoupper($this->name);
    }

    #[MapSetter('name')]
    public function setName(string $name = 'unused'): void
    {
        $this->name = 'set:' . $name;
    }
}
