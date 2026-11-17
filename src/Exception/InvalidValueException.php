<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Mapper\Diagnostics\Exception\ViolationException;
use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationCode;

/**
 * Value cannot be converted by the requested type. A type throws it with
 * the path relative to its own value, every composite type the error passes
 * through prepends its segment.
 */
final class InvalidValueException extends ViolationException implements MapperExceptionInterface
{
    /**
     * @param non-empty-string $expected
     */
    public static function becauseInvalidType(string $expected, mixed $value): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::InvalidType, ['expected' => $expected], new Path(), $value),
        );
    }

    /**
     * The path of the error points to the key itself.
     *
     * @param non-empty-string $expected
     */
    public static function becauseInvalidKey(string $expected, int|string $key): self
    {
        return self::fromViolation(new Violation(
            ViolationCode::InvalidKey,
            ['expected' => $expected],
            new Path([new KeySegment($key)]),
            $key,
        ));
    }

    /**
     * The path of the error points to the place of the field.
     *
     * @param non-empty-string $field
     */
    public static function becauseFieldIsMissing(string $field, SegmentInterface $segment): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::MissingField, ['field' => $field], new Path([$segment])),
        );
    }

    /**
     * The path of the error points to the place of the duplicate field.
     *
     * @param non-empty-string $field
     * @param non-empty-string $other the field given before
     */
    public static function becauseFieldIsDuplicated(
        string $field,
        string $other,
        SegmentInterface $segment,
        mixed $value,
    ): self {
        return self::fromViolation(new Violation(
            ViolationCode::DuplicateField,
            ['field' => $field, 'other' => $other],
            new Path([$segment]),
            $value,
        ));
    }

    /**
     * The path of the error points to the place of the field. The name of
     * the field comes from the data, its control characters, quotes and
     * backslashes are escaped.
     */
    public static function becauseFieldIsUnexpected(string $field, SegmentInterface $segment, mixed $value): self
    {
        $name = ['field' => \addcslashes($field, "\0..\37\"\\\177")];

        return self::fromViolation(
            new Violation(ViolationCode::UnexpectedField, $name, new Path([$segment]), $value),
        );
    }

    /**
     * The value is rejected by an error of another kind, such as an error
     * of a hook of a class.
     */
    public static function becauseValueIsRejected(\Throwable $error, mixed $value): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::InvalidValue, ['message' => $error->getMessage()], new Path(), $value),
            $error,
        );
    }

    /**
     * @param non-empty-string $expected
     * @param non-empty-string $check
     */
    public static function becauseCheckFailed(string $expected, string $check, mixed $value): self
    {
        return self::fromViolation(new Violation(
            ViolationCode::FailedCheck,
            ['expected' => $expected, 'check' => $check],
            new Path(),
            $value,
        ));
    }

    /**
     * Returns the error with the segment prepended to the paths of its
     * violations. An error of another kind becomes one violation of the
     * value at the segment. The template, such as a text of the user for a
     * property, replaces the messages of the violations of the value itself,
     * the violations of its nested values keep theirs.
     *
     * The trace of the returned error does not hold the given error, the
     * memory of an error of nested data does not grow with its depth.
     *
     * @param non-empty-string|null $template
     */
    public static function wrapWithPath(
        \Throwable $error,
        SegmentInterface $segment,
        mixed $value,
        ?string $template = null,
    ): self {
        if (!$error instanceof self) {
            $violations = [new Violation(
                ViolationCode::InvalidValue,
                ['message' => $error->getMessage()],
                new Path([$segment]),
                $value,
                $template,
            )];

            return new self($violations, self::formatSummary($violations), $error);
        }

        $previous = $error->getPrevious();
        $violations = [];

        foreach ($error->violations as $violation) {
            $own = $template;

            if ($own === null || $violation->path->segments !== []) {
                $own = $violation->template;
            }

            $violations[] = new Violation(
                $violation->code,
                $violation->parameters,
                new Path([$segment, ...$violation->path->segments]),
                $violation->value,
                $own,
            );
        }

        unset($error);

        return new self($violations, self::formatSummary($violations), $previous);
    }

    /**
     * Adds the violations of the error to the violations a composite type
     * collects to go on with its other values.
     *
     * @param list<Violation> $violations
     * @param int<1, max> $limit
     * @throws self in case of the limit of the violations is reached: the
     *         error as is in case of nothing was collected before, the
     *         collected violations cut to the limit otherwise
     */
    public static function collect(array &$violations, self $error, int $limit): void
    {
        if ($violations === [] && \count($error->violations) === $limit) {
            throw $error;
        }

        foreach ($error->violations as $violation) {
            $violations[] = $violation;
        }

        if (\count($violations) < $limit) {
            return;
        }

        /** @var non-empty-list<Violation> */
        $result = \array_slice($violations, 0, $limit);

        unset($error);

        throw new self($result, self::formatSummary($result));
    }

    /**
     * Returns the error of the violations a composite type has collected.
     *
     * @param non-empty-list<Violation> $violations
     */
    public static function becauseViolationsAreCollected(array $violations): self
    {
        return new self($violations, self::formatSummary($violations));
    }

    /**
     * Returns the error with the message of each violation written by the
     * template, such as a text of the user for a property, the error as is
     * in case of the template is {@see null}.
     *
     * @param non-empty-string|null $template
     */
    public function withTemplate(?string $template): self
    {
        if ($template === null) {
            return $this;
        }

        $violations = [];

        foreach ($this->violations as $violation) {
            $violations[] = $violation->withTemplate($template);
        }

        return new self($violations, self::formatSummary($violations), $this->getPrevious());
    }

    private static function fromViolation(Violation $violation, ?\Throwable $previous = null): self
    {
        return new self([$violation], self::formatSummary([$violation]), $previous);
    }
}
