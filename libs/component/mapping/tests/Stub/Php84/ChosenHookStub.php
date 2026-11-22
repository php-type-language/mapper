<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;

final class ChosenHookStub
{
    public string $title = '' {
        #[MapGetter]
        #[MapType('non-empty-string')]
        get => \strtoupper($this->title);
        #[MapSetter]
        set(int|string $value) {
            $this->title = (string) $value;
        }
    }
}
