<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reader\Stub;

final class TypesHooksClass
{
    public string $withGetHook {
        get => 'x';
    }

    public int $withSetHook {
        set(int|null $value) {}
    }
}
