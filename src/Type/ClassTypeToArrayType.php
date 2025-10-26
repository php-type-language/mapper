<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Context\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Exception\Mapping\FinalExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueOfTypeException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\PropertyAccessor\PropertyAccessorInterface;

/**
 * @template TObject of object = object
 * @template-implements TypeInterface<object|array<array-key, mixed>>
 */
class ClassTypeToArrayType implements TypeInterface
{
    public function __construct(
        /**
         * @var ClassMetadata<TObject>
         */
        protected readonly ClassMetadata $metadata,
        protected readonly PropertyAccessorInterface $accessor,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        $class = $this->metadata->name;

        return $value instanceof $class;
    }

    public function cast(mixed $value, Context $context): object|array
    {
        $className = $this->metadata->name;

        if (!$value instanceof $className) {
            throw InvalidValueOfTypeException::createFromContext(
                expected: $this->metadata->getTypeStatement($context, read: true),
                value: $value,
                context: $context,
            );
        }

        // Subtype normalization
        if ($value::class !== $className) {
            /** @var object|array<array-key, mixed> */
            return $context->getTypeByValue($value)
                ->cast($value, $context);
        }

        $entrance = $context->enter($value, new ObjectEntry($this->metadata->name));

        $result = $this->normalizeObject($value, $entrance);

        if ($this->metadata->isNormalizeAsArray ?? $context->isObjectsAsArrays()) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * @return array<array-key, mixed>
     * @throws InvalidObjectValueException in case the value of a certain field is incorrect
     * @throws \Throwable in case of internal error occurs
     */
    protected function normalizeObject(object $object, Context $context): array
    {
        $result = [];

        foreach ($this->metadata->properties as $meta) {
            $entrance = $context->enter(
                value: $object,
                entry: new ObjectPropertyEntry($meta->name),
                isStrictTypes: $meta->read->strict,
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
            } catch (FinalExceptionInterface $e) {
                throw $e;
            } catch (\Throwable $e) {
                $exception = InvalidObjectValueException::createFromContext(
                    element: $element,
                    field: $meta->alias,
                    expected: $meta->getTypeStatement($entrance, read: true),
                    value: $object,
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
