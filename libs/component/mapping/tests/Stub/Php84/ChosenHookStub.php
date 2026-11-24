<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

/**
 * Property read and written through its hooks.
 */
final class ChosenHookStub
{
    public string $title = '' {
        #[MapGetter(type: 'non-empty-string')]
        get => \strtoupper($this->title);
        #[MapSetter('heading')]
        set(int|string $value) {
            $this->title = (string) $value;
        }
    }
}
