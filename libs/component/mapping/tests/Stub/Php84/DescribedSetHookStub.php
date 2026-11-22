<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub\Php84;

final class DescribedSetHookStub
{
    private string $stored = '';

    /**
     * @var non-empty-string
     */
    public string $name {
        get => $this->stored;
        /**
         * Stores the value.
         */
        set {
            $this->stored = $value;
        }
    }
}
