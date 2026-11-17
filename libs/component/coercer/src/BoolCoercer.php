<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

/**
 * Converts `1`, `0` and case-insensitive boolean words, such as `'true'`
 * or `'off'`, to `bool`.
 *
 * @template-implements CoercerInterface<bool>
 */
final class BoolCoercer implements CoercerInterface
{
    private const WORDS = [
        '1' => true,
        '0' => false,
        'true' => true,
        'false' => false,
        'yes' => true,
        'no' => false,
        'on' => true,
        'off' => false,
    ];

    public function coerce(mixed $value): ?bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (\is_string($value)) {
            return self::WORDS[$value] ?? self::WORDS[\strtolower($value)] ?? null;
        }

        if ($value === 1) {
            return true;
        }

        if ($value === 0) {
            return false;
        }

        return null;
    }
}
