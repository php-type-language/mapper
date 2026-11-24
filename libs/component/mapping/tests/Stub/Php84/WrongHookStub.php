<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\SkipWhenNull;

/**
 * Attributes of the hooks given where they are not allowed.
 */
final class WrongHookStub
{
    public string $wrongHook = '' {
        #[MapSetter]
        get => $this->wrongHook;
    }

    #[MapGetter]
    public string $twice = '' {
        #[MapGetter]
        get => $this->twice;
    }

    #[MapGetter]
    public string $virtual {
        get => 'virtual';
    }

    #[MapType('string')]
    public string $helped {
        get => 'helped';
    }

    #[SkipWhenNull]
    public ?string $skippedOnProperty = null {
        #[MapGetter]
        get => $this->skippedOnProperty;
    }

    public string $typedHook = '' {
        #[MapType('string')]
        get => $this->typedHook;
    }

    public ?string $skippedOnSetHook = null {
        #[SkipWhenNull]
        set(?string $value) {
            $this->skippedOnSetHook = $value;
        }
    }
}
