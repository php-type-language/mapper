<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Members declared by the attributes of the access between the properties
 * of the selector.
 */
final class DeclarationOrderStub
{
    #[MapGetter, MapSetter]
    private int $id = 0;

    public string $name = '';

    #[MapSetter('first')]
    public function setFirst(string $first): void {}

    public int $age = 0;

    #[MapGetter('first')]
    public function getFirst(): string
    {
        return (string) $this->id;
    }
}
