<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming\Internal;

/**
 * Name split into words by case boundaries, digits and separators, with
 * the separators before the first word and after the last one.
 *
 * Bytes `A-Z` are uppercase letters, bytes `a-z` and all non-ASCII bytes
 * are lowercase letters, `0-9` are digits, anything else is a separator.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Naming
 */
final class Name
{
    /**
     * Word is one of:
     *
     * - an optional uppercase letter followed by lowercase letters and
     *   digits: `Server`, `utf8`;
     * - a plural abbreviation, a run of uppercase letters with `s` not
     *   followed by a lowercase letter: `URLs`, `IDs`;
     * - a run of uppercase letters not followed by a lowercase letter with
     *   optional trailing digits: `HTTP`, `HTTP2`.
     *
     * @var non-empty-string
     */
    private const WORD_PATTERN = '/[A-Z]?[a-z0-9\x80-\xff]+'
        . '|[A-Z]{2,}s(?![a-z\x80-\xff])'
        . '|[A-Z]+(?![a-z\x80-\xff])(?:[0-9][a-z0-9\x80-\xff]*)?/';

    public function __construct(
        /**
         * Separators before the first word: `__` of `__typename`
         */
        public readonly string $prefix,
        /**
         * Words in their original case, empty in case of the name has no
         * letters and digits
         *
         * @var list<non-empty-string>
         */
        public readonly array $words,
        /**
         * Separators after the last word, the whole name in case of the name
         * has no words
         */
        public readonly string $suffix,
    ) {}

    public static function parse(string $name): self
    {
        \preg_match_all(self::WORD_PATTERN, $name, $matches, \PREG_OFFSET_CAPTURE);

        /** @var list<array{non-empty-string, int<0, max>}> $found */
        $found = $matches[0];

        if ($found === []) {
            return new self('', [], $name);
        }

        [$last, $offset] = $found[\array_key_last($found)];

        return new self(
            prefix: \substr($name, 0, $found[0][1]),
            words: \array_column($found, 0),
            suffix: \substr($name, $offset + \strlen($last)),
        );
    }

    /**
     * Returns the name with the words in the given cases joined by the
     * separator, the separators around the words are kept.
     */
    public function join(string $separator, WordCase $first, WordCase $rest): string
    {
        $words = $this->words;

        if ($words !== []) {
            $words[0] = $first->apply($words[0]);
        }

        for ($i = 1, $count = \count($words); $i < $count; ++$i) {
            $words[$i] = $rest->apply($words[$i]);
        }

        return $this->prefix . \implode($separator, $words) . $this->suffix;
    }
}
