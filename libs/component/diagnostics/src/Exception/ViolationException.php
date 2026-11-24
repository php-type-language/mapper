<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Diagnostics\Exception;

use TypeLang\Mapper\Diagnostics\Printer\Path\PathPrinterInterface;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationFormatterInterface;

/**
 * Mapping failure caused by invalid input. Iterates over the violations in
 * order of appearance, a fatal one tells a defect of the code behind the
 * input, see {@see Violation::$isFatal}.
 *
 * @template-implements \IteratorAggregate<int, Violation>
 */
abstract class ViolationException extends \RuntimeException implements \IteratorAggregate, \Countable
{
    /**
     * Whether a violation is caused by an {@see \Error}
     */
    public readonly bool $hasFatalViolations;

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

        $this->hasFatalViolations = $this->getFatalViolations() !== [];
    }

    /**
     * @return \ArrayIterator<int, Violation>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->violations);
    }

    /**
     * @return int<1, max>
     */
    public function count(): int
    {
        return \count($this->violations);
    }

    /**
     * Returns the violations caused by an {@see \Error}, in order of
     * appearance.
     *
     * @return list<Violation>
     */
    public function getFatalViolations(): array
    {
        $result = [];

        foreach ($this->violations as $violation) {
            if ($violation->isFatal) {
                $result[] = $violation;
            }
        }

        return $result;
    }

    /**
     * Returns the errors of the fatal violations by their violations.
     *
     * @return iterable<Violation, \Error>
     */
    public function getFatalErrors(): iterable
    {
        foreach ($this->violations as $violation) {
            if ($violation->cause instanceof \Error) {
                yield $violation => $violation->cause;
            }
        }
    }

    /**
     * Returns the causes of the violations that have one, in order of
     * appearance.
     *
     * @return list<\Throwable>
     */
    public function getCauses(): array
    {
        $result = [];

        foreach ($this->violations as $violation) {
            if ($violation->cause !== null) {
                $result[] = $violation->cause;
            }
        }

        return $result;
    }

    /**
     * Returns the first violation with its path and the number of the
     * others: `$.items[3].id: Value must be ... (and 2 more)`.
     *
     * @param non-empty-list<Violation> $violations
     */
    protected static function formatSummary(
        array $violations,
        ViolationFormatterInterface $formatter,
        PathPrinterInterface $pathPrinter,
    ): string {
        $first = $violations[0];
        $summary = $pathPrinter->print($first->path) . ': ' . $formatter->format($first);

        if (\count($violations) === 1) {
            return $summary;
        }

        return \sprintf('%s (and %d more)', $summary, \count($violations) - 1);
    }
}
