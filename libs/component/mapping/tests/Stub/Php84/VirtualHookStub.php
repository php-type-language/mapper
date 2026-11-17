<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class VirtualHookStub
{
    public string $name = 'name';

    public string $computed {
        get => 'Hello ' . $this->name;
    }

    public string $alias {
        set(string $value) {
            $this->name = $value;
        }
    }

    public string $backed = 'backed' {
        get => $this->backed;
    }
}
