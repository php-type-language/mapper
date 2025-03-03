<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type\ClassType;

use TypeLang\Mapper\Exception\Mapping\FinalExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueOfTypeException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Runtime\PropertyAccessor\PropertyAccessorInterface;

/**
 * @template T of object
 */
class ClassTypeNormalizer implements TypeInterface
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        protected readonly ClassMetadata $metadata,
        protected readonly PropertyAccessorInterface $accessor,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        $class = $this->metadata->getName();

        return $value instanceof $class;
    }

    /**
     * @return object|array<non-empty-string, mixed>
     * @throws InvalidObjectValueException in case the value of a certain field is incorrect
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, Context $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueOfTypeException::createFromContext(
                expected: $this->metadata->getTypeStatement($context),
                value: $value,
                context: $context,
            );
        }

        // Subtype normalization
        if ($value::class !== $className) {
            /** @var object|array<non-empty-string, mixed> */
            return $context->getTypeByValue($value)
                ->cast($value, $context);
        }

        $entrance = $context->enter($value, new ObjectEntry($this->metadata->getName()));

        $result = $this->normalizeObject($value, $entrance);

        if ($this->metadata->isNormalizeAsArray() ?? $context->isObjectsAsArrays()) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * @param T $object
     *
     * @return array<non-empty-string, mixed>
     * @throws InvalidObjectValueException in case the value of a certain field is incorrect
     * @throws \Throwable in case of internal error occurs
     */
    protected function normalizeObject(object $object, Context $context): array
    {
        $result = [];

        foreach ($this->metadata->getProperties() as $meta) {
            $entrance = $context->enter($object, new ObjectPropertyEntry($meta->getName()));

            // Skip the property when not readable
            if (!$this->accessor->isReadable($object, $meta->getName())) {
                continue;
            }

            $element = $this->accessor->getValue($object, $meta->getName());

            // Skip the property when condition is matched
            foreach ($meta->getSkipConditions() as $condition) {
                if ($condition->match($object, $element)) {
                    continue 2;
                }
            }

            // Fetch field type
            $info = $meta->findTypeInfo();
            $type = $info !== null ? $info->getType() : $context->getTypeByDefinition('mixed');

            try {
                // Insert field value into result
                $result[$meta->getExportName()] = $type->cast($element, $entrance);
            } catch (FinalExceptionInterface $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw InvalidObjectValueException::createFromContext(
                    element: $element,
                    field: $meta->getExportName(),
                    expected: $meta->getTypeStatement($entrance),
                    value: $object,
                    context: $entrance,
                    previous: $e,
                );
            }
        }

        return $result;
    }
}
