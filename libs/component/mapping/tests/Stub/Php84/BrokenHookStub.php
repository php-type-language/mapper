<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

final class BrokenHookStub
{
    private string $stored = 'title';

    /**
     * @var non-empty-string
     */
    public string $title {
        #[MapGetter]
        get => $this->stored;
        /**
         * @param list<int $title
         */
        #[MapSetter]
        set(string $title) {
            $this->stored = $title;
        }
    }
}
