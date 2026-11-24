<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Path;

use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;
use TypeLang\Mapper\Diagnostics\Printer\Name\NameSanitizer;

/**
 * Prints a path as JSONPath: `$.items[3].name`, `$['a b']`.
 *
 * A name of a segment is cut to 64 characters and ends with `...` inside the
 * quotes: `$['aaaa...']`, such a path is not exact. Bytes that are not valid
 * UTF-8 are printed as `\xFF`, the result is always valid UTF-8.
 *
 * The path follows JSONPath except for the escapes of invalid bytes and the
 * cut names.
 *
 * @link https://www.rfc-editor.org/rfc/rfc9535
 */
final class JsonPathPrinter implements PathPrinterInterface
{
    private const ESCAPES = [
        '\\' => '\\\\',
        '\'' => '\\\'',
        "\x08" => '\b',
        "\t" => '\t',
        "\n" => '\n',
        "\f" => '\f',
        "\r" => '\r',
    ];

    /**
     * Prints steps of the input only, other segments are skipped.
     *
     * @return non-empty-string
     */
    public function print(Path $path): string
    {
        $result = '$';

        foreach ($path->segments as $segment) {
            $result .= self::printSegment($segment);
        }

        return $result;
    }

    private static function printSegment(SegmentInterface $segment): string
    {
        return match (true) {
            $segment instanceof PropertySegment => self::printName($segment->property),
            $segment instanceof KeySegment => self::printKey($segment->key),
            default => '',
        };
    }

    /**
     * @return non-empty-string
     */
    private static function printKey(int|string $key): string
    {
        if (\is_int($key) && $key >= 0) {
            return '[' . $key . ']';
        }

        return self::printName((string) $key);
    }

    /**
     * @return non-empty-string
     */
    private static function printName(string $name): string
    {
        $name = NameSanitizer::limit($name);

        if (\preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $name) === 1) {
            return '.' . $name;
        }

        return '[\'' . self::escape($name) . '\']';
    }

    private static function escape(string $name): string
    {
        $escaped = \strtr($name, self::ESCAPES);

        $escaped = (string) \preg_replace_callback(
            pattern: '/[\x00-\x1f]/',
            callback: static fn(array $char): string => \sprintf('\u%04x', \ord($char[0])),
            subject: $escaped,
        );

        return NameSanitizer::escapeInvalidBytes($escaped);
    }
}
