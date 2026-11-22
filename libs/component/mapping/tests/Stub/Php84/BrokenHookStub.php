<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class BrokenHookStub
{
    private string $stored = 'title';

    /**
     * @var non-empty-string
     */
    public string $title {
        get => $this->stored;
        /**
         * @param list<int $title
         */
        set(string $title) {
            $this->stored = $title;
        }
    }
}
