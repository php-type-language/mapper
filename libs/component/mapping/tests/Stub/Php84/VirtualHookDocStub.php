<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class VirtualHookDocStub
{
    private mixed $stored = null;

    /**
     * @var non-empty-string
     */
    public string $name {
        get => (string) $this->stored;
        set(int|string $value) {
            $this->stored = $value;
        }
    }

    /**
     * @var list<int>
     */
    public array $items {
        get => (array) $this->stored;
        set {
            $this->stored = $value;
        }
    }

    public string $title {
        /**
         * @return non-empty-string
         */
        get => (string) $this->stored;
        /**
         * @param positive-int|non-empty-string $title
         */
        set(int|string $title) {
            $this->stored = $title;
        }
    }
}
