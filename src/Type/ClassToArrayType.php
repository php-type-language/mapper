<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Context\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Runtime\InvalidValueOfTypeException;
use TypeLang\Mapper\Exception\Runtime\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * @template TObject of object = object
 * @template-implements TypeInterface<object|array<array-key, mixed>>
 */
class ClassToArrayType implements TypeInterface
{
    public function __construct(
        /**
         * @var ClassMetadata<TObject>
         */
        protected readonly ClassMetadata $metadata,
        protected readonly PropertyAccessorInterface $accessor,
    ) {}

    public function match(mixed $value, RuntimeContext $context): bool
    {
        $class = $this->metadata->name;

        return $value instanceof $class;
    }

    public function cast(mixed $value, RuntimeContext $context): object|array
    {
        $className = $this->metadata->name;

        /**
         * Do not allow non-compatible types: The `$value` instance
         * must be compatible with declared type.
         */
        if (!$value instanceof $className) {
            throw InvalidValueOfTypeException::createFromContext(
                expected: $this->metadata->getTypeStatement($context, read: true),
                context: $context,
            );
        }

        /**
         * Force subtype normalization in case of:
         *
         * ```php
         * public AbstractClass $obj = new ClassImpl();
         * ```
         *
         * Then the `$className` will contain `AbstractClass`,
         * and `$value::class` will contain `ClassImpl`.
         */
        if ($value::class !== $className) {
            $type = $context->getTypeByValue($value);

            /**
             * Most likely, the `$type` will return the same result as the
             * current type.
             *
             * However, this is not guaranteed.
             *
             * @var object|array<array-key, mixed>
             */
            return $type->cast($value, $context);
        }

        $entrance = $context->enter(
            value: $value,
            entry: new ObjectEntry($this->metadata->name),
        );

        $result = $this->normalize($value, $entrance);

        if ($this->metadata->isNormalizeAsArray ?? $context->isObjectAsArray()) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidObjectValueException in case the value of a certain field is incorrect
     * @throws \Throwable in case of internal error occurs
     */
    protected function normalize(object $object, RuntimeContext $context): array
    {
        $result = [];

        foreach ($this->metadata->properties as $meta) {
            $entrance = $context->enter(
                value: $object,
                entry: new ObjectPropertyEntry($meta->name),
                config: $context->withStrictTypes($meta->read->strict),
            );

            // Skip the property when not readable
            if (!$this->accessor->isReadable($object, $meta->name)) {
                continue;
            }

            $element = $this->accessor->getValue($object, $meta->name);

            // Skip the property when condition is matched
            foreach ($meta->skip as $condition) {
                if ($condition->match($object, $element)) {
                    continue 2;
                }
            }

            try {
                // Insert field value into result
                $result[$meta->alias] = $meta->read->type->cast($element, $entrance);
            } catch (NotInterceptableExceptionInterface $e) {
                throw $e;
            } catch (\Throwable $e) {
                $exception = InvalidObjectValueException::createFromContext(
                    element: $element,
                    field: $meta->alias,
                    expected: $meta->getTypeStatement($entrance, read: true),
                    context: $entrance,
                    previous: $e,
                );

                if ($meta->typeErrorMessage !== null) {
                    $exception->updateMessage($meta->typeErrorMessage);
                }

                throw $exception;
            }
        }

        return $result;
    }
}
