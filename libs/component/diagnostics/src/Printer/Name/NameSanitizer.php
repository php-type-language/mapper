<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Name;

/**
 * Makes a name that comes from the input data safe to be a part of a message:
 * the name is cut to a limited length and the result is valid UTF-8.
 *
 * A name that is not valid UTF-8 is treated as bytes as a whole.
 *
 * Use {@see self::limit()} first, then escape the quotes of the target
 * format, then {@see self::escapeInvalidBytes()}.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class NameSanitizer
{
    private const MAX_LENGTH = 64;

    private const CUT_MARK = '...';

    /**
     * Cuts the name to {@see self::MAX_LENGTH} characters, or to
     * {@see self::MAX_LENGTH} bytes when the name is not valid UTF-8.
     */
    public static function limit(string $name): string
    {
        if (\strlen($name) <= self::MAX_LENGTH) {
            return $name;
        }

        if (\preg_match('//u', $name) !== 1) {
            return \substr($name, 0, self::MAX_LENGTH) . self::CUT_MARK;
        }

        if (\preg_match('/^.{' . self::MAX_LENGTH . '}(?=.)/us', $name, $matches) !== 1) {
            return $name;
        }

        return $matches[0] . self::CUT_MARK;
    }

    /**
     * Replaces every non-ASCII byte of a name that is not valid UTF-8 with
     * its hexadecimal escape: `\xFF`.
     */
    public static function escapeInvalidBytes(string $name): string
    {
        if (\preg_match('//u', $name) === 1) {
            return $name;
        }

        return (string) \preg_replace_callback(
            pattern: '/[\x80-\xFF]/',
            callback: static fn(array $byte): string => \sprintf('\\x%02X', \ord($byte[0])),
            subject: $name,
        );
    }
}
