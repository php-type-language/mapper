<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;

final class UnparsableTypeStub
{
    /**
     * @var array<mixed>
     */
    #[MapType('list<Usr')]
    public array $items = [];
}
