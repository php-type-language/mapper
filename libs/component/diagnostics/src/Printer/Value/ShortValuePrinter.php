<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Value;

use TypeLang\Mapper\Diagnostics\RedactedValue;

/**
 * Prints a value for a developer: scalars and strings by value, arrays and
 * objects by kind (`array(3)`, `object(App\User)`), a hidden value as
 * `*redacted*`.
 */
final class ShortValuePrinter implements ValuePrinterInterface
{
    private const ESCAPED_CHARS = "\0..\37\"\\\177";

    public function __construct(
        /**
         * Maximum number of characters of a string to show, `0` hides the
         * contents and prints only the length in bytes: `string(12)`
         *
         * @var int<0, max>
         */
        private readonly int $maxStringLength = 40,
    ) {}

    /**
     * @return non-empty-string
     */
    public function print(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            $value === true => 'true',
            $value === false => 'false',
            \is_int($value) => (string) $value,
            \is_float($value) => self::printFloat($value),
            \is_string($value) => $this->printString($value),
            \is_array($value) => \sprintf('array(%d)', \count($value)),
            $value instanceof RedactedValue => (string) $value,
            $value instanceof \UnitEnum => $value::class . '::' . $value->name,
            \is_object($value) => \sprintf('object(%s)', \get_debug_type($value)),
            \is_resource($value) => \sprintf('resource(%s)', \get_resource_type($value)),
            default => 'resource(closed)',
        };
    }

    /**
     * @return non-empty-string
     */
    private static function printFloat(float $value): string
    {
        /** @var non-empty-string */
        return \var_export($value, true);
    }

    /**
     * @return non-empty-string
     */
    private function printString(string $value): string
    {
        if ($this->maxStringLength === 0) {
            return \sprintf('string(%d)', \strlen($value));
        }

        $head = $value;
        $headLength = ($this->maxStringLength + 2) * 4;

        // the beginning enough for the shown characters, without a cut one
        if (\strlen($value) > $headLength) {
            $head = (string) \preg_replace('/[\xC0-\xF7][\x80-\xBF]*$/D', '', \substr($value, 0, $headLength));
        }

        $chars = \preg_split('//u', $head, $this->maxStringLength + 1, \PREG_SPLIT_NO_EMPTY);

        if ($chars === false) {
            return \sprintf('string(%d)', \strlen($value));
        }

        if (\count($chars) <= $this->maxStringLength) {
            return '"' . \addcslashes($value, self::ESCAPED_CHARS) . '"';
        }

        $shown = \implode('', \array_slice($chars, 0, $this->maxStringLength));

        return '"' . \addcslashes($shown, self::ESCAPED_CHARS) . '"...';
    }
}
