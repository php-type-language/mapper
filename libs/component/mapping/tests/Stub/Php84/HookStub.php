<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class HookStub
{
    /**
     * @var non-empty-string
     */
    public string $name = 'name' {
        set(int|string $value) {
            $this->name = (string) $value;
        }
    }

    public string $title = 'title' {
        /**
         * @return non-empty-string
         */
        get => $this->title;
        /**
         * @param positive-int|non-empty-string $title
         */
        set(int|string $title) {
            $this->title = (string) $title;
        }
    }
}
