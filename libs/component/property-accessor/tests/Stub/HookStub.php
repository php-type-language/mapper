<?php

declare(strict_types=1);

namespace TypeLang\Mapper\PropertyAccessor\Tests\Stub;

final class HookStub
{
    /**
     * Virtual, computed, read-only property (no backing store).
     */
    public string $virtualReadOnly {
        get => 'computed';
    }

    /**
     * Virtual, write-only property whose set hook rejects any value.
     */
    public string $virtualWriteOnly {
        set (string $value) {
            throw new \DomainException('Rejected ' . $value);
        }
    }

    /**
     * Non-virtual property with hooks over its own backing store.
     */
    public string $hooked = 'raw-init' {
        get => 'via-get-hook';
        set (string $value) => $this->hooked = 'via-set-hook:' . $value;
    }
}
