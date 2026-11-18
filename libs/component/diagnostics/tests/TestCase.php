<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\Printer\Value\ValuePrinterInterface;
use TypeLang\Mapper\Diagnostics\PublicViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Printer\PrinterOptions;
use TypeLang\Printer\TypePrinterInterface;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the formatter with the short values and the types in a single
     * line unless the printers are given.
     */
    protected static function createFormatter(
        ?ValuePrinterInterface $values = null,
        ?TypePrinterInterface $types = null,
        bool $shouldPrintDeclaredType = false,
    ): ViolationFormatter {
        return new ViolationFormatter(
            $values ?? new ShortValuePrinter(),
            $types ?? self::createTypePrinter(),
            $shouldPrintDeclaredType,
        );
    }

    protected static function createPublicFormatter(?ValuePrinterInterface $values = null): PublicViolationFormatter
    {
        if ($values === null) {
            return new PublicViolationFormatter(self::createTypePrinter());
        }

        return new PublicViolationFormatter(self::createTypePrinter(), $values);
    }

    protected static function createTypePrinter(): TypePrinterInterface
    {
        return new PrettyTypePrinter(new PrinterOptions(maxInlineShapeFields: \PHP_INT_MAX));
    }
}
