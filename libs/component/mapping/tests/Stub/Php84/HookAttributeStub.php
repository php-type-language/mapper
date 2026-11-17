<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Tests\Stub\UserStub as Account;

final class HookAttributeStub
{
    #[MapType('non-empty-string')]
    public string $name = 'name' {
        get => $this->name;
        #[MapType('int|string', strict: true)]
        set(int|string $value) {
            $this->name = (string) $value;
        }
    }

    public string $title = 'title' {
        #[MapType('Account')]
        get => $this->title;
    }

    public string $code = 'code' {
        #[MapType('numeric-string')]
        set(string $value) {
            $this->code = $value;
        }
    }
}
