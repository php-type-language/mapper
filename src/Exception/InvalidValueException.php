<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Mapper\Diagnostics\Exception\ViolationException;
use TypeLang\Mapper\Diagnostics\Path\Path;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;
use TypeLang\Mapper\Diagnostics\Printer\Name\NameSanitizer;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\PublicViolationFormatter;
use TypeLang\Mapper\Diagnostics\Violation;
use TypeLang\Mapper\Diagnostics\ViolationCode;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatterInterface;
use TypeLang\Mapper\Exception\InvalidValueException\DeclaredTypeFinder;
use TypeLang\Mapper\Type\Expander\TypeExpander;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Printer\PrinterOptions;
use TypeLang\Type\TypeNode;

/**
 * Value cannot be converted by the requested type. A type throws it with
 * the path relative to its own value, every composite type the error passes
 * through prepends its segment.
 *
 * A type does not know how it is written, the declared type of a violation
 * is set by the declaration the error passes through: a property of a
 * class or the type given to the mapper. The mapper gives the violations
 * their expected types: the declared ones without the classes, enums and
 * constants of the application.
 *
 * The message of an error thrown by the mapper is written for a reader
 * outside of the application by {@see PublicViolationFormatter}: with the
 * expected types and without the messages of other errors. The declared
 * types and the causes are kept in the violations, a message for the
 * developer is rendered from them by {@see ViolationFormatter}.
 */
final class InvalidValueException extends ViolationException implements MapperExceptionInterface
{
    /**
     * @param TypeNode|null $declared type the value is declared with, such
     *        as the values of the tags of a discriminator, set by the
     *        declaration in case of {@see null}
     */
    public static function becauseInvalidType(mixed $value, ?TypeNode $declared = null): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::InvalidType, [], new Path(), $value, declared: $declared),
        );
    }

    /**
     * The path of the error points to the key itself.
     */
    public static function becauseInvalidKey(int|string $key): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::InvalidKey, [], new Path([new KeySegment($key)]), $key),
        );
    }

    /**
     * The path of the error points to the place of the field.
     *
     * @param non-empty-string $field
     * @param TypeNode|null $declared type of the field, {@see null} in case
     *        of it is unknown
     */
    public static function becauseFieldIsMissing(
        string $field,
        SegmentInterface $segment,
        ?TypeNode $declared = null,
    ): self {
        return self::fromViolation(new Violation(
            ViolationCode::MissingField,
            ['field' => $field],
            new Path([$segment]),
            declared: $declared,
        ));
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
     * the field comes from the data: it is cut to 64 characters, its control
     * characters, quotes and backslashes are escaped, bytes that are not
     * valid UTF-8 are printed as `\xFF`.
     */
    public static function becauseFieldIsUnexpected(string $field, SegmentInterface $segment, mixed $value): self
    {
        $name = ['field' => NameSanitizer::escapeInvalidBytes(
            \addcslashes(NameSanitizer::limit($field), "\0..\37\"\\\177"),
        )];

        return self::fromViolation(
            new Violation(ViolationCode::UnexpectedField, $name, new Path([$segment]), $value),
        );
    }

    /**
     * The value is rejected by an error of another kind, such as an error
     * of a hook of a class. The error is the cause of the violation and the
     * previous error, its message is not a part of the violation.
     */
    public static function becauseValueIsRejected(\Throwable $error, mixed $value): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::InvalidValue, [], new Path(), $value, cause: $error),
            $error,
        );
    }

    /**
     * The value of an object cannot be read, such as an uninitialized
     * property. The error is the cause of the violation and the previous
     * error, the violation has no value.
     */
    public static function becauseValueIsUnreadable(\Throwable $error): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::UnreadableValue, [], new Path(), null, cause: $error),
            $error,
        );
    }

    /**
     * The path of the error points to the value nested deeper than the
     * limit.
     *
     * @param int<1, max> $limit
     */
    public static function becauseDepthIsExceeded(int $limit, mixed $value): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::DepthExceeded, ['limit' => (string) $limit], new Path(), $value),
        );
    }

    /**
     * @param class-string $base class with the discriminator
     */
    public static function becauseObjectIsNotTagged(string $base, object $value): self
    {
        return self::fromViolation(new Violation(
            ViolationCode::UntaggedObject,
            ['class' => $value::class, 'base' => $base],
            new Path(),
            $value,
        ));
    }

    /**
     * @param non-empty-string $check
     */
    public static function becauseCheckFailed(string $check, mixed $value): self
    {
        return self::fromViolation(
            new Violation(ViolationCode::FailedCheck, ['check' => $check], new Path(), $value),
        );
    }

    /**
     * Returns the error with the segment prepended to the paths of its
     * violations. An error of another kind becomes one violation of the
     * value at the segment with the error as its cause. The template, such
     * as a text of the user for a property, replaces the messages of the
     * violations of the value itself, the violations of its nested values
     * keep theirs.
     *
     * The type of the value at the segment, such as the declared type of a
     * property, gives the declared type to the violations without it, the
     * finder tells the types of the collections in it.
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
        ?TypeNode $type = null,
        DeclaredTypeFinder $finder = new DeclaredTypeFinder(),
    ): self {
        if (!$error instanceof self) {
            $violations = [new Violation(
                ViolationCode::InvalidValue,
                [],
                new Path([$segment]),
                $value,
                $template,
                $type,
                cause: $error,
            )];

            return new self($violations, self::summarize($violations), $error);
        }

        $previous = $error->getPrevious();
        $violations = [];

        foreach ($error->violations as $violation) {
            $violation = self::resolveDeclared($violation, $type, $finder)
                ->withPath(new Path([$segment, ...$violation->path->segments]));

            if ($template !== null && \count($violation->path->segments) === 1) {
                $violation = $violation->withTemplate($template);
            }

            $violations[] = $violation;
        }

        unset($error);

        return new self($violations, self::summarize($violations), $previous);
    }

    /**
     * Returns the error with the declared types of the violations without
     * them found by their paths in the type of the value, such as the type
     * given to the mapper, the finder tells the types of the collections in
     * it. The error as is in case of none is found.
     */
    public function withDeclaredType(TypeNode $type, DeclaredTypeFinder $finder = new DeclaredTypeFinder()): self
    {
        $violations = [];
        $isChanged = false;

        foreach ($this->violations as $violation) {
            $found = self::resolveDeclared($violation, $type, $finder);
            $isChanged = $isChanged || $found !== $violation;
            $violations[] = $found;
        }

        if (!$isChanged) {
            return $this;
        }

        return new self($violations, self::summarize($violations), $this->getPrevious());
    }

    /**
     * Returns the error with the expected types of the violations expanded
     * from their declared types and the message written by them for a reader
     * outside of the application.
     */
    public function withExpectedTypes(TypeExpander $expander, ViolationFormatterInterface $formatter): self
    {
        $violations = [];

        /**
         * Expansions by the ids of the declared types, the violations of the
         * elements of one collection share the node of its element type.
         * {@see null} for a type that cannot be expanded.
         *
         * @var array<int, TypeNode|null> $expansions
         */
        $expansions = [];

        foreach ($this->violations as $violation) {
            if ($violation->declared === null || $violation->expected !== null) {
                $violations[] = $violation;

                continue;
            }

            $id = \spl_object_id($violation->declared);

            if (!\array_key_exists($id, $expansions)) {
                $expansions[$id] = self::expand($expander, $violation->declared);
            }

            $violations[] = $violation->withExpected($expansions[$id]);
        }

        return new self($violations, self::formatSummary($violations, $formatter, new JsonPathPrinter()), $this->getPrevious());
    }

    /**
     * Returns the error with the message written by the formatter, such as
     * for a developer on normalization: the expected types describe the
     * data of a client, not the values of the application.
     */
    public function withPublicMessage(ViolationFormatterInterface $formatter): self
    {
        return new self(
            $this->violations,
            self::formatSummary($this->violations, $formatter, new JsonPathPrinter()),
            $this->getPrevious(),
        );
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

        throw self::becauseViolationsAreCollected($result);
    }

    /**
     * Returns the error of the violations a composite type has collected,
     * the cause of the first violation with a cause is the previous error.
     *
     * @param non-empty-list<Violation> $violations
     */
    public static function becauseViolationsAreCollected(array $violations): self
    {
        $previous = null;

        foreach ($violations as $violation) {
            if ($violation->cause !== null) {
                $previous = $violation->cause;

                break;
            }
        }

        return new self($violations, self::summarize($violations), $previous);
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

        return new self($violations, self::summarize($violations), $this->getPrevious());
    }

    /**
     * Returns the expected type of the declared one, {@see null} in case of
     * it cannot be expanded: the violation is written without a type and
     * not with the names of the application.
     */
    private static function expand(TypeExpander $expander, TypeNode $declared): ?TypeNode
    {
        try {
            return $expander->expand($declared);
        } catch (TypeExpansionException) {
            return null;
        }
    }

    /**
     * Returns the violation with the declared type found by its path in the
     * type of the value, the violation as is in case of it has the declared
     * type or none is found.
     */
    private static function resolveDeclared(
        Violation $violation,
        ?TypeNode $type,
        DeclaredTypeFinder $finder,
    ): Violation {
        if ($type === null || $violation->declared !== null) {
            return $violation;
        }

        $isKey = $violation->code === ViolationCode::InvalidKey;
        $declared = $finder->find($type, $violation->path->segments, $isKey);

        if ($declared === null) {
            return $violation;
        }

        return $violation->withDeclared($declared);
    }

    private static function fromViolation(Violation $violation, ?\Throwable $previous = null): self
    {
        return new self([$violation], self::summarize([$violation]), $previous);
    }

    /**
     * Returns the message of an error before the mapper writes it by its
     * formatter, such as of an error of a type used alone: for a reader
     * outside of the application.
     *
     * @param non-empty-list<Violation> $violations
     */
    private static function summarize(array $violations): string
    {
        return self::formatSummary(
            $violations,
            new PublicViolationFormatter(new PrettyTypePrinter(new PrinterOptions(maxInlineShapeFields: \PHP_INT_MAX))),
            new JsonPathPrinter(),
        );
    }
}
