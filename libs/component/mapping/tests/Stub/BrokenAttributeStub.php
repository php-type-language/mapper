<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapName;
use TypeLang\Mapper\Mapping\MapType;

final class BrokenAttributeStub
{
    // @phpstan-ignore-next-line the argument is missing on purpose
    #[MapName]
    public int $id = 0;

    /**
     * @var array<mixed>
     */
    #[MapType('array<')]
    public array $items = [];

    #[MapName('')]
    public string $name = '';
}
