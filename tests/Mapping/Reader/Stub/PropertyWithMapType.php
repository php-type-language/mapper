<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapType;

final class PropertyWithMapType
{
    #[MapType('list<int>')]
    public array $items;
}
