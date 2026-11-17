<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class BrokenHookStub
{
    /**
     * @var non-empty-string
     */
    public string $title = 'title' {
        /**
         * @param list<int $title
         */
        set(string $title) {
            $this->title = $title;
        }
    }
}
