<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Coercer;

/**
 * Converts integers and finite floats to their decimal notation.
 *
 * A float is written in the shortest form that is read back to the same
 * number, as {@see \json_encode()} does by default, regardless of the
 * `serialize_precision` ini setting.
 *
 * @template-implements CoercerInterface<string>
 */
final class StringCoercer implements CoercerInterface
{
    /**
     * Value of the `serialize_precision` ini setting for the shortest notation
     */
    private const SHORTEST_PRECISION = '-1';

    public function coerce(mixed $value): ?string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (\is_int($value)) {
            return (string) $value;
        }

        if (\is_float($value)) {
            return self::coerceFloat($value);
        }

        return null;
    }

    private static function coerceFloat(float $value): ?string
    {
        if (!\is_finite($value)) {
            return null;
        }

        if (\ini_get('serialize_precision') === self::SHORTEST_PRECISION) {
            return self::encodeFloat($value);
        }

        $previous = \ini_set('serialize_precision', self::SHORTEST_PRECISION);

        try {
            return self::encodeFloat($value);
        } finally {
            \ini_set('serialize_precision', (string) $previous);
        }
    }

    private static function encodeFloat(float $value): ?string
    {
        $result = \json_encode($value);

        if ($result === false) {
            return null;
        }

        return $result;
    }
}
