<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapName;

final class PropertyWithMapName
{
    #[MapName('custom_name')]
    public string $property;
}
