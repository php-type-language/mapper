<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Mapping\MapType;

final class BypassedSetHookStub
{
    public string $tags = '' {
        #[MapType('list<string>')]
        set(array|string $value) {
            $this->tags = \is_array($value) ? \implode(',', $value) : $value;
        }
    }
}
