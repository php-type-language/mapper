<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Profiler;

use TypeLang\Mapper\Diagnostics\Exception\ViolationException;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Printer\PrinterOptions;

/**
 * A finished call of a traced mapper.
 */
final class MapperCall
{
    public function __construct(
        /**
         * Name of the mapper
         *
         * @var non-empty-string
         */
        public readonly string $mapper,
        /**
         * Name of the method of {@see \TypeLang\Mapper\MapperInterface}
         *
         * @var non-empty-string
         */
        public readonly string $method,
        public readonly ?string $type,
        public readonly mixed $input,
        /**
         * Result of the call, {@see null} in case of an error
         */
        public readonly mixed $output,
        public readonly ?\Throwable $error,
        /**
         * Duration of the call in milliseconds
         */
        public readonly float $duration,
        /**
         * File of the code calling the mapper
         */
        public readonly ?string $file = null,
        public readonly ?int $line = null,
    ) {}

    /**
     * Returns the violations of the error for the developer: with the
     * declared types and the messages of their causes, such as of an error
     * of a hook.
     *
     * @return list<array{path: non-empty-string, message: string}>
     */
    public function getViolations(): array
    {
        if (!$this->error instanceof ViolationException) {
            return [];
        }

        $formatter = new ViolationFormatter(
            new ShortValuePrinter(),
            new PrettyTypePrinter(new PrinterOptions(maxInlineShapeFields: \PHP_INT_MAX)),
            shouldPrintDeclaredType: true,
        );

        $printer = new JsonPathPrinter();
        $result = [];

        foreach ($this->error->violations as $violation) {
            $message = $formatter->format($violation);

            if ($violation->cause !== null && $violation->cause->getMessage() !== '') {
                $message .= ': ' . $violation->cause->getMessage();
            }

            $result[] = [
                'path' => $printer->print($violation->path),
                'message' => $message,
            ];
        }

        return $result;
    }
}
