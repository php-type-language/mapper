<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

/**
 * Rejected value of a sensitive field in place of the value itself.
 */
final class RedactedValue implements \Stringable
{
    /**
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return '*redacted*';
    }
}
