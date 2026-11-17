<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Exception;

use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\Printer\Path\PathPrinterInterface;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatterInterface;

/**
 * Mapping failure caused by invalid input.
 */
abstract class ViolationException extends \RuntimeException
{
    protected function __construct(
        /**
         * Violations in order of appearance
         *
         * @var non-empty-list<Violation>
         */
        public readonly array $violations,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns the first violation with its path and the number of the
     * others: `$.items[3].id: Value must be ... (and 2 more)`.
     *
     * @param non-empty-list<Violation> $violations
     */
    protected static function formatSummary(
        array $violations,
        ViolationFormatterInterface $formatter = new ViolationFormatter(),
        PathPrinterInterface $pathPrinter = new JsonPathPrinter(),
    ): string {
        $first = $violations[0];
        $summary = $pathPrinter->print($first->path) . ': ' . $formatter->format($first);

        if (\count($violations) === 1) {
            return $summary;
        }

        return \sprintf('%s (and %d more)', $summary, \count($violations) - 1);
    }
}
