<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Naming\Internal;

/**
 * Case of a word in a key. Only ASCII letters change, other bytes and the
 * locale are ignored.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Naming
 */
enum WordCase
{
    /**
     * All letters are lowercase: `user`.
     */
    case Lower;

    /**
     * All letters are uppercase: `USER`.
     */
    case Upper;

    /**
     * Uppercase first letter, lowercase others: `User`.
     */
    case Capitalized;

    private const UPPERCASE_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private const LOWERCASE_LETTERS = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * @param non-empty-string $word
     * @return non-empty-string
     */
    public function apply(string $word): string
    {
        return match ($this) {
            self::Lower => \strtr($word, self::UPPERCASE_LETTERS, self::LOWERCASE_LETTERS),
            self::Upper => \strtr($word, self::LOWERCASE_LETTERS, self::UPPERCASE_LETTERS),
            self::Capitalized => \strtr($word[0], self::LOWERCASE_LETTERS, self::UPPERCASE_LETTERS)
                . \strtr(\substr($word, 1), self::UPPERCASE_LETTERS, self::LOWERCASE_LETTERS),
        };
    }
}
