<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception;

/**
 * @internal This is an internal library class, please do not use it in your code.
 * @psalm-internal Serafim\Mapper
 */
final class StringInfo
{
    /**
     * @param non-empty-string $letter
     */
    public static function isVowel(string $letter): bool
    {
        assert($letter !== '');

        $lower = \strtolower($letter);

        return \in_array($lower, ['a', 'e', 'i', 'o', 'u'], true);
    }

    /**
     * @param non-empty-string $word
     * @return 'an'|'a'
     */
    public static function getArticle(string $word): string
    {
        assert($word !== '');

        // Extract first letter from the word (sentence).
        \preg_match('/[a-z]/iu', $word, $matches);

        if ($matches === []) {
            return 'a';
        }

        return self::isVowel($matches[0]) ? 'an' : 'a';
    }

    /**
     * @param non-empty-string $word
     * @return non-empty-string
     */
    public static function withArticle(string $word): string
    {
        return self::getArticle($word) . ' ' . $word;
    }

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
     * @param list<non-empty-string> $values
     * @return non-empty-string
     */
    public static function formatRangeValues(array $values): string
    {
        $last = \array_pop($values);

        $values[\array_key_last($values)] = \end($values) . ' or ' . $last;

        return \implode(', ', $values);
    }

    /**
     * @param non-empty-string $value
     * @return non-empty-string
     */
    public static function quoted(string $value): string
    {
        return \vsprintf('"%s"', [
            \addcslashes($value, '"'),
        ]);
    }
}
