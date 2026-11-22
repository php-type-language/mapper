<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class WiderSetHookStub
{
    public string $list = '' {
        set(array|string $value) {
            $this->list = \is_array($value) ? \implode(',', $value) : $value;
        }
    }

    public string $number = '' {
        set(int|string $value) {
            $this->number = 'n' . $value;
        }
    }

    public string $id = '' {
        set(string|\Stringable $value) {
            $this->id = (string) $value;
        }
    }

    public string $virtual {
        get => $this->number;
        set(int|string $value) {
            $this->number = (string) $value;
        }
    }
}
