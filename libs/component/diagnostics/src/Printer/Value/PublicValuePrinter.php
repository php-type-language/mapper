<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Printer\Value;

use TypeLang\Mapper\Diagnostics\RedactedValue;

/**
 * Prints a value for a reader outside of the application: an object as
 * `object`, a case of an enum as its value or name, a resource as
 * `resource`, a hidden value as `*redacted*`. Scalars and arrays are
 * printed by the inner printer.
 */
final class PublicValuePrinter implements ValuePrinterInterface
{
    public function __construct(
        private readonly ValuePrinterInterface $printer = new ShortValuePrinter(),
    ) {}

    public function print(mixed $value): string
    {
        return match (true) {
            $value instanceof RedactedValue => (string) $value,
            $value instanceof \BackedEnum => $this->printer->print($value->value),
            $value instanceof \UnitEnum => $this->printer->print($value->name),
            \is_object($value) => 'object',
            \is_resource($value), \gettype($value) === 'resource (closed)' => 'resource',
            default => $this->printer->print($value),
        };
    }
}
