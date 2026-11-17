<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Request\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use TypeLang\Mapper\Diagnostics\Exception\ViolationException;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;

/**
 * Data of the request cannot be mapped to a controller argument or the
 * mapped value is not valid, answered with `422 Unprocessable Content`.
 *
 * @phpstan-type RequestViolationType array{
 *     path: non-empty-string,
 *     message: string,
 *     code: string,
 * }
 */
final class InvalidRequestException extends HttpException
{
    /**
     * @param non-empty-list<RequestViolationType> $violations
     */
    private function __construct(
        /**
         * Violations in order of appearance, the path is a JSONPath of the
         * data
         *
         * @var non-empty-list<RequestViolationType>
         */
        public readonly array $violations,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            message: self::formatSummary($violations),
            previous: $previous,
        );
    }

    public static function becauseDataIsNotMapped(ViolationException $e): self
    {
        $formatter = new ViolationFormatter();
        $printer = new JsonPathPrinter();
        $violations = [];

        foreach ($e->violations as $violation) {
            $violations[] = [
                'path' => $printer->print($violation->path),
                'message' => $formatter->format($violation),
                'code' => $violation->code->getCode(),
            ];
        }

        return new self($violations, $e);
    }

    /**
     * The path of a violation is the property path of the validator, the
     * names of the PHP properties.
     *
     * @param ConstraintViolationListInterface $list non-empty list
     */
    public static function becauseValueIsInvalid(ConstraintViolationListInterface $list): self
    {
        $violations = [];

        foreach ($list as $violation) {
            $path = $violation->getPropertyPath();

            $violations[] = [
                'path' => match (true) {
                    $path === '' => '$',
                    \str_starts_with($path, '[') => '$' . $path,
                    default => '$.' . $path,
                },
                'message' => (string) $violation->getMessage(),
                'code' => (string) $violation->getCode(),
            ];
        }

        \assert($violations !== []);

        return new self($violations);
    }

    /**
     * Returns the first violation with its path and the number of the
     * others: `$.items[3].id: Value must be ... (and 2 more)`.
     *
     * @param non-empty-list<RequestViolationType> $violations
     */
    private static function formatSummary(array $violations): string
    {
        $summary = $violations[0]['path'] . ': ' . $violations[0]['message'];

        if (\count($violations) === 1) {
            return $summary;
        }

        return \sprintf('%s (and %d more)', $summary, \count($violations) - 1);
    }
}
