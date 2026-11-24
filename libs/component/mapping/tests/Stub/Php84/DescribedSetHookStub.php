<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;

final class DescribedSetHookStub
{
    private string $stored = '';

    /**
     * @var non-empty-string
     */
    public string $name {
        #[MapGetter]
        get => $this->stored;
        /**
         * Stores the value.
         */
        #[MapSetter]
        set {
            $this->stored = $value;
        }
    }
}
