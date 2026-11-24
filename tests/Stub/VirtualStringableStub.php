<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

final class VirtualStringableStub
{
    private string $stored = '';

    public string $id {
        #[MapGetter]
        get => $this->stored;
        #[MapSetter]
        set(string|\Stringable $value) {
            $this->stored = (string) $value;
        }
    }
}
