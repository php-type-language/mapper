<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

final class VirtualHookDocStub
{
    private mixed $stored = null;

    /**
     * @var non-empty-string
     */
    public string $name {
        #[MapGetter]
        get => (string) $this->stored;
        #[MapSetter]
        set(int|string $value) {
            $this->stored = $value;
        }
    }

    /**
     * @var list<int>
     */
    public array $items {
        #[MapGetter]
        get => (array) $this->stored;
        #[MapSetter]
        set {
            $this->stored = $value;
        }
    }

    public string $title {
        /**
         * @return non-empty-string
         */
        #[MapGetter]
        get => (string) $this->stored;
        /**
         * @param positive-int|non-empty-string $title
         */
        #[MapSetter]
        set(int|string $title) {
            $this->stored = $title;
        }
    }
}
