<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics;

use TypeLang\Mapper\Diagnostics\Printer\Value\PublicValuePrinter;
use TypeLang\Mapper\Diagnostics\Printer\Value\ValuePrinterInterface;
use TypeLang\Printer\TypePrinterInterface;

/**
 * Renders a violation for a reader outside of the application, such as a
 * client of an API: with the expected type and the value printed by
 * {@see PublicValuePrinter}. A violation without the expected type is
 * rendered without the type.
 *
 * ```
 * // list<App\Status>
 * $formatter->format($violation);
 * // Value must be of type list<'new'|'paid'>, but "x" given
 * ```
 */
final class PublicViolationFormatter implements ViolationFormatterInterface
{
    private readonly ViolationFormatterInterface $formatter;

    public function __construct(
        TypePrinterInterface $typePrinter,
        ValuePrinterInterface $valuePrinter = new PublicValuePrinter(),
    ) {
        $this->formatter = new ViolationFormatter(
            valuePrinter: $valuePrinter,
            typePrinter: $typePrinter,
            shouldPrintDeclaredType: false,
        );
    }

    public function format(Violation $violation): string
    {
        return $this->formatter->format($violation);
    }
}
