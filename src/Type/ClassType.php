<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;
use TypeLang\Mapper\Mapping\Metadata\AliasesMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\ExpressionSkipCondition;
use TypeLang\Mapper\PropertyAccessor\PropertyWriterInterface;
use TypeLang\Mapper\Type\ClassType\Field;
use TypeLang\Mapper\Type\ClassType\TagCheck;
use TypeLang\Mapper\Type\Extractor\LiteralTypeExtractor;
use TypeLang\Type\Name;

/**
 * Object of a class: created and filled from an array or a
 * {@see \stdClass} on denormalization, turned into an array of its fields
 * on normalization. A {@see \stdClass} is accepted in the strict context
 * too.
 *
 * The type holds the plan only: the metadata and the types of the fields
 * of each direction. The instantiator of the class, the readers and writers
 * of the fields and the hooks of the class are taken from
 * {@see Context::$accessors} by the metadata. The hooks before a conversion
 * run in its check too.
 *
 * On denormalization a field is taken by its name or by an alias, several
 * of them present are resolved by the conflict of the aliases.
 *
 * A field that cannot be read on normalization, such as an uninitialized
 * property or a get hook that throws, is a fault of the object: its error
 * becomes a violation with the path of the field. A field whose skip
 * condition is matched by its value is left out of the data and is not
 * checked, an {@see \Exception} of the condition is a violation with the
 * path of the field.
 *
 * @template T of object
 *
 * @template-implements TypeInterface<T>
 */
final class ClassType implements TypeInterface
{
    /**
     * Keys of the data the class takes, the other keys are extra
     *
     * @var array<array-key, true>
     */
    private readonly array $keys;

    public function __construct(
        /**
         * Description of the class the type is built from
         *
         * @var ClassMetadata<T>
         */
        public readonly ClassMetadata $metadata,
        /**
         * Fields of the denormalization, with the types of the written
         * values
         *
         * @var list<Field<InputFieldMetadata, mixed>>
         */
        public readonly array $writable,
        /**
         * Fields of the normalization, with the types of the read values
         *
         * @var list<Field<OutputFieldMetadata, mixed>>
         */
        public readonly array $readable,
        /**
         * Keys of the data the class takes besides its properties: the
         * fields of the tags of its bases with a discriminator
         *
         * @var list<string>
         */
        public readonly array $tagFields = [],
        /**
         * Tags of the class written first on normalization, by the fields of
         * the discriminators of its bases, the root ones first
         *
         * @var array<non-empty-string, int|string>
         */
        public readonly array $tags = [],
        /**
         * Tags the data may have in the fields of the discriminators on
         * denormalization
         *
         * @var list<TagCheck>
         */
        public readonly array $tagChecks = [],
    ) {
        $keys = \array_fill_keys($tagFields, true);

        foreach ($writable as $property) {
            $keys[$property->metadata->name] = true;

            foreach ($property->metadata->aliases->names ?? [] as $alias) {
                $keys[$alias] = true;
            }
        }

        // the normalized data of the class is not extra, such as a field of
        // a property that is only read
        foreach ($readable as $property) {
            $keys[$property->metadata->name] = true;
        }

        $this->keys = $keys;
    }

    /**
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        try {
            foreach ($context->accessors->get($this->metadata)->beforeDenormalization as $hook) {
                $value = $hook->beforeDenormalize($this->metadata, $value, $context);
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception) {
            return false;
        }

        $value = $context->coercers->array->coerce($value);

        if ($value === null) {
            return false;
        }

        foreach ($this->tagChecks as $check) {
            if (\array_key_exists($check->field, $value) && !$check->isAccepted($value[$check->field])) {
                return false;
            }
        }

        foreach ($this->writable as $property) {
            $metadata = $property->metadata;
            $aliases = $metadata->aliases;
            $key = $metadata->name;

            if ($aliases !== null) {
                $present = self::getPresentNames($metadata->name, $aliases, $value);

                if ($present !== []) {
                    $key = self::selectName($metadata->name, $aliases, $present, $value);
                }

                if ($key === null) {
                    return false;
                }
            }

            if (!\array_key_exists($key, $value)) {
                if ($metadata->isOptional) {
                    continue;
                }

                return false;
            }

            $isStrict = $metadata->strict;
            $current = $isStrict === null ? $context : $context->withStrict($isStrict);

            if (!$property->type->isDenormalizable($value[$key], $current)) {
                return false;
            }
        }

        return !$context->config->rejectExtraFields
            || \array_diff_key($value, $this->keys) === [];
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value or one of its
     *         fields cannot be denormalized
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function denormalize(mixed $value, Context $context): object
    {
        $accessor = $context->accessors->get($this->metadata);
        $input = $value;

        try {
            foreach ($accessor->beforeDenormalization as $hook) {
                $value = $hook->beforeDenormalize($this->metadata, $value, $context);
            }

            $input = $value;
            $value = $context->coercers->array->coerce($input)
                ?? throw InvalidValueException::becauseInvalidType($input)
                    ->withTemplate($this->metadata->input->typeErrorMessage);

            $object = $accessor->instantiator->instantiate($value);
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $input);
        }

        $limit = $context->config->maxViolations;
        $violations = $writes = [];

        foreach ($this->tagChecks as $check) {
            $field = $check->field;

            if (\array_key_exists($field, $value) && !$check->isAccepted($value[$field])) {
                InvalidValueException::collect($violations, self::createTagException($check, $value[$field]), $limit);
            }
        }

        foreach ($this->writable as $property) {
            $metadata = $property->metadata;
            $aliases = $metadata->aliases;
            $key = $metadata->name;
            $present = [];

            if ($aliases !== null) {
                $present = self::getPresentNames($metadata->name, $aliases, $value);

                if ($present !== []) {
                    $key = self::selectName($metadata->name, $aliases, $present, $value);
                }
            }

            if ($key === null) {
                foreach (\array_slice($present, 1) as $duplicate) {
                    InvalidValueException::collect(
                        $violations,
                        InvalidValueException::becauseFieldIsDuplicated(
                            $duplicate,
                            $present[0],
                            new PropertySegment($duplicate),
                            $value[$duplicate],
                        ),
                        $limit,
                    );
                }

                continue;
            }

            if (!\array_key_exists($key, $value)) {
                $default = $metadata->default;

                if ($default !== null) {
                    $writes[] = [$property, $key, $default->value, $default->value, false];

                    continue;
                }

                // a setter with an optional parameter is not called
                if ($metadata->isOptional) {
                    continue;
                }

                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::becauseFieldIsMissing(
                        $key,
                        new PropertySegment($key),
                        $property->declared,
                    )
                        ->withTemplate($metadata->undefinedErrorMessage),
                    $limit,
                );

                continue;
            }

            $field = $value[$key];
            $isStrict = $metadata->strict;

            try {
                $converted = $property->type->denormalize(
                    $field,
                    $isStrict === null ? $context : $context->withStrict($isStrict),
                );

                $writes[] = [$property, $key, $field, $converted, true];
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::wrapWithPath(
                        $e,
                        new PropertySegment($key),
                        $field,
                        $metadata->typeErrorMessage,
                        $property->declared,
                        $context->declaredTypes,
                    ),
                    $limit,
                );
            }
        }

        if ($context->config->rejectExtraFields) {
            foreach (\array_diff_key($value, $this->keys) as $key => $field) {
                InvalidValueException::collect(
                    $violations,
                    InvalidValueException::becauseFieldIsUnexpected((string) $key, new KeySegment($key), $field),
                    $limit,
                );
            }
        }

        // the object takes the values once all of them are accepted
        if ($violations === []) {
            foreach ($writes as [$property, $key, $field, $converted, $isFromData]) {
                $error = self::write($accessor->writers[$property->index], $object, $converted);

                if ($error === null) {
                    continue;
                }

                if (!$isFromData) {
                    $error = InvalidValueException::wrapWithPath($error, new PropertySegment($key), $field);
                    InvalidValueException::collect($violations, $error, $limit);

                    continue;
                }

                $error = InvalidValueException::wrapWithPath(
                    $error,
                    new PropertySegment($key),
                    $field,
                    $property->metadata->typeErrorMessage,
                    $property->declared,
                    $context->declaredTypes,
                );
                InvalidValueException::collect($violations, $error, $limit);
            }
        }

        if ($violations !== []) {
            throw InvalidValueException::becauseViolationsAreCollected($violations);
        }

        try {
            foreach ($accessor->afterDenormalization as $hook) {
                $hook->afterDenormalize($this->metadata, $object, $input, $context);
            }
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $input);
        }

        return $object;
    }

    /**
     * Checks the object as deep as {@see self::normalize()} does: each
     * property must be readable, such as initialized, and of its type.
     *
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function isNormalizable(mixed $value, Context $context): bool
    {
        if (!$value instanceof $this->metadata->name) {
            return false;
        }

        $nesting = $context->nesting;

        if ($nesting->depth >= $context->config->maxDepth) {
            return false;
        }

        ++$nesting->depth;

        try {
            return $this->isObjectNormalizable($value, $context);
        } finally {
            --$nesting->depth;
        }
    }

    /**
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    private function isObjectNormalizable(object $value, Context $context): bool
    {
        $accessor = $context->accessors->get($this->metadata);

        try {
            foreach ($accessor->beforeNormalization as $hook) {
                $value = $hook->beforeNormalize($this->metadata, $value, $context);
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception) {
            return false;
        }

        foreach ($this->readable as $property) {
            try {
                $field = $accessor->readers[$property->index]->read($value);
            } catch (\Throwable) {
                return false;
            }

            try {
                if ($property->metadata->skip !== [] && self::isSkipped($property, $value, $field, $context)) {
                    continue;
                }
            } catch (\Throwable) {
                return false;
            }

            if (!$property->type->isNormalizable($field, $context)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the values of the properties by their keys in the data, as
     * changed by the hooks: an array or a {@see \stdClass} by the class or
     * by the configuration.
     *
     * @return array<array-key, mixed>|\stdClass
     * @throws InvalidValueException in case of the value is not an object of
     *         the class, is nested deeper than the limit or one of its
     *         properties cannot be normalized
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    public function normalize(mixed $value, Context $context): array|\stdClass
    {
        if (!$value instanceof $this->metadata->name) {
            throw InvalidValueException::becauseInvalidType($value);
        }

        $nesting = $context->nesting;

        if ($nesting->depth >= $context->config->maxDepth) {
            throw InvalidValueException::becauseDepthIsExceeded($context->config->maxDepth, $value);
        }

        ++$nesting->depth;

        try {
            return $this->normalizeObject($value, $context);
        } finally {
            --$nesting->depth;
        }
    }

    /**
     * @return array<array-key, mixed>|\stdClass
     * @throws InvalidValueException in case of one of the properties cannot
     *         be normalized
     * @throws TypeDefinitionException in case of a strategy rejects the
     *         class or a property
     */
    private function normalizeObject(object $value, Context $context): array|\stdClass
    {
        $accessor = $context->accessors->get($this->metadata);
        $object = $value;

        try {
            foreach ($accessor->beforeNormalization as $hook) {
                $value = $hook->beforeNormalize($this->metadata, $value, $context);
            }
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $object);
        }

        $result = [];

        foreach ($this->readable as $property) {
            $key = $property->metadata->name;

            try {
                $field = $accessor->readers[$property->index]->read($value);
            } catch (\Throwable $e) {
                throw InvalidValueException::wrapWithPath(
                    InvalidValueException::becauseValueIsUnreadable($e),
                    new PropertySegment($key),
                    null,
                );
            }

            try {
                if ($property->metadata->skip !== [] && self::isSkipped($property, $value, $field, $context)) {
                    continue;
                }
            } catch (\Throwable $e) {
                throw InvalidValueException::wrapWithPath($e, new PropertySegment($key), $field);
            }

            try {
                $result[$key] = $property->type->normalize($field, $context);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Exception $e) {
                throw InvalidValueException::wrapWithPath(
                    $e,
                    new PropertySegment($key),
                    $field,
                    null,
                    $property->declared,
                    $context->declaredTypes,
                );
            }
        }

        try {
            foreach ($accessor->afterNormalization as $hook) {
                $result = $hook->afterNormalize($this->metadata, $value, $result, $context);
            }
        } catch (NotInterceptableExceptionInterface|InvalidValueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw InvalidValueException::becauseValueIsRejected($e, $object);
        }

        // the tag replaces a field of its name
        if ($this->tags !== []) {
            $result = $this->tags + $result;
        }

        if ($this->metadata->output->normalizeAsArray ?? $context->config->normalizeAsArray) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * Writes the value, a failure of the write is returned as the error: a
     * {@see \TypeError}, such as of a property of another type than the type
     * of the field, an error or an exception of a setter or a hook.
     *
     * @param PropertyWriterInterface<object, mixed> $writer
     */
    private static function write(PropertyWriterInterface $writer, object $object, mixed $value): ?\Exception
    {
        try {
            $writer->write($object, $value);
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            return $e;
        } catch (\Throwable $e) {
            return InvalidValueException::becauseValueIsRejected($e, $value);
        }

        return null;
    }

    /**
     * Returns the error of the tag of another class, with the path of the
     * field.
     */
    private static function createTagException(TagCheck $check, mixed $tag): InvalidValueException
    {
        $declared = null;

        if ($check->tags !== []) {
            $declared = LiteralTypeExtractor::extractAll(\array_keys($check->tags));
        }

        return InvalidValueException::wrapWithPath(
            InvalidValueException::becauseInvalidType($tag, $declared),
            new PropertySegment($check->field),
            $tag,
        );
    }

    /**
     * Returns the names of the field present in the data, in the order of
     * priority: the name, then the aliases.
     *
     * @param non-empty-string $name
     * @param array<array-key, mixed> $value
     * @return list<non-empty-string>
     */
    private static function getPresentNames(string $name, AliasesMetadata $aliases, array $value): array
    {
        $present = [];

        foreach ([$name, ...$aliases->names] as $candidate) {
            if (\array_key_exists($candidate, $value)) {
                $present[] = $candidate;
            }
        }

        return $present;
    }

    /**
     * Returns the name the value of the field is taken from by its alias
     * conflict, {@see null} in case of the present names conflict.
     *
     * @param non-empty-string $name
     * @param non-empty-list<non-empty-string> $present names of the field
     *        present in the data
     * @param array<array-key, mixed> $value
     * @return non-empty-string|null
     */
    private static function selectName(string $name, AliasesMetadata $aliases, array $present, array $value): ?string
    {
        if (\count($present) === 1) {
            return $present[0];
        }

        $conflict = $aliases->conflict;
        $isNameFirst = $conflict === AliasConflict::UseNameOrFirst || $conflict === AliasConflict::UseNameOrLast;

        if ($isNameFirst && \in_array($name, $present, true)) {
            return $name;
        }

        return match ($conflict) {
            AliasConflict::Fail => null,
            AliasConflict::UseFirst, AliasConflict::UseNameOrFirst
                => self::sortByData($present, $value)[0],
            AliasConflict::UseLast, AliasConflict::UseNameOrLast
                => \array_reverse(self::sortByData($present, $value))[0],
        };
    }

    /**
     * Returns the names in the order of their keys in the data.
     *
     * @param non-empty-list<non-empty-string> $names
     * @param array<array-key, mixed> $value
     * @return non-empty-list<non-empty-string>
     */
    private static function sortByData(array $names, array $value): array
    {
        $positions = [];

        foreach (\array_keys($value) as $position => $key) {
            $positions[(string) $key] = $position;
        }

        \usort($names, static fn(string $a, string $b): int => $positions[$a] <=> $positions[$b]);

        return $names;
    }

    /**
     * Returns {@see true} in case of a skip condition of the property is
     * matched by the read value. An expression is evaluated by the language
     * of the context, in case of it is given.
     *
     * @param Field<OutputFieldMetadata, mixed> $property
     * @throws \ErrorException in case of a condition gives a notice or a
     *         warning
     */
    private static function isSkipped(Field $property, object $object, mixed $field, Context $context): bool
    {
        \set_error_handler(self::throwError(...));

        try {
            return self::isMatched($property, $object, $field, $context);
        } finally {
            \restore_error_handler();
        }
    }

    /**
     * A notice or a warning of an expression, such as of an unknown
     * property, is the exception of the condition, whatever the level of
     * the reported errors.
     *
     * @throws \ErrorException in case of the error is reported
     */
    private static function throwError(int $severity, string $message, string $file, int $line): never
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * @param Field<OutputFieldMetadata, mixed> $property
     * @throws \ErrorException in case of a condition gives a notice or a
     *         warning
     */
    private static function isMatched(Field $property, object $object, mixed $field, Context $context): bool
    {
        $expressions = $context->expressions;

        foreach ($property->metadata->skip as $condition) {
            if ($expressions !== null && $condition instanceof ExpressionSkipCondition) {
                if ((bool) $expressions->evaluate($condition->expression, [
                    ExpressionSkipCondition::VALUE => $field,
                    $condition->variable => $object,
                ])) {
                    return true;
                }

                continue;
            }

            if ($condition->isMatched($object, $field)) {
                return true;
            }
        }

        return false;
    }
}
