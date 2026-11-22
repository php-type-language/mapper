<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapType;

final class BrokenHookAttributeStub
{
    private string $stored = '';

    public string $title = '' {
        #[MapType('non-empty-string')]
        get => \strtoupper($this->title);
    }

    public string $tags = '' {
        #[MapType('list<string>')]
        set(array|string $value) {
            $this->tags = \is_array($value) ? \implode(',', $value) : $value;
        }
    }

    public string $code {
        #[MapType('string', strict: true)]
        get => $this->stored;
        set(string $value) {
            $this->stored = $value;
        }
    }
}
