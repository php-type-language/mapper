<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

use TypeLang\Mapper\Mapping\MapType;

final class PropertyWithHookAttributes
{
    #[MapType('string')]
    public int $propertyWithType {
        get => (string) $this->propertyWithType;
    }

    public string $withGetHook {
        #[MapType('int')]
        get => 42;
    }

    public string $withSetHook {
        #[MapType('string|\Stringable')]
        set(string|\Stringable $value) {}
    }
}
