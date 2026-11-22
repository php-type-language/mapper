<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Account;

final class HookAttributeStub
{
    private string $stored = '';

    #[MapType('non-empty-string')]
    public string $name {
        get => $this->stored;
        #[MapType('int|string', strict: true)]
        set(int|string $value) {
            $this->stored = (string) $value;
        }
    }

    public string $title {
        #[MapType('Account')]
        get => $this->stored;
        set(string $value) {
            $this->stored = $value;
        }
    }

    public string $code {
        get => $this->stored;
        #[MapType('numeric-string')]
        set(string $value) {
            $this->stored = $value;
        }
    }
}
