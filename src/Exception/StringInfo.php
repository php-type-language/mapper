<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class StringInfo
{
    /**
     * @return non-empty-string
     */
    public static function formatRange(int $from, int $to): string
    {
        if ($from === $to) {
            return (string) $from;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return self::formatRangeValues(\range($from, $to));
    }

    /**
     * @param list<scalar> $values
     *
     * @return non-empty-string
     */
    public static function formatRangeValues(array $values): string
    {
        $last = \array_pop($values);

        $values[\array_key_last($values)] = \end($values) . ' or ' . $last;

        return \implode(', ', $values);
    }

    /**
     * @return non-empty-string
     */
    public static function quoted(string $value): string
    {
        return \vsprintf('"%s"', [
            \addcslashes($value, '"'),
        ]);
    }
}
