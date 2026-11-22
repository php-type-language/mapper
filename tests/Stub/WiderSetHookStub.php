<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

/**
 * Properties with a backing store whose `set` hooks take more than the
 * type of the property: the mapper writes the store, the hooks are not
 * called.
 */
final class WiderSetHookStub
{
    public string $list = '' {
        set(array|string $value) {
            $this->list = \is_array($value) ? \implode(',', $value) : $value;
        }
    }

    public string $number = '' {
        set(int|string $value) {
            $this->number = 'hook:' . $value;
        }
    }

    public string $id = '' {
        set(string|\Stringable $value) {
            $this->id = 'hook:' . $value;
        }
    }
}
