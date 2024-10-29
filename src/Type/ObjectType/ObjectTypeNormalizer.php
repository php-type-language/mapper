<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ObjectType;

use TypeLang\Mapper\Exception\Mapping\FieldExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidFieldTypeValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueMappingException;
use TypeLang\Mapper\Exception\Mapping\MappingExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\MissingFieldTypeException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Type\ObjectType\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template T of object
 */
class ObjectTypeNormalizer implements TypeInterface
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
     * @throws InvalidValueException
     * @throws \Throwable
     */
    public function cast(mixed $value, Context $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueMappingException::createFromContext(
                value: $value,
                expected: $this->metadata->getTypeStatement($context),
                context: $context,
            );
        }

        $entrance = $context->enter($value, new ObjectEntry($this->metadata->getName()));

        $result = $this->normalizeObject($value, $entrance);

        if ($context->isObjectsAsArrays()) {
            return $result;
        }

        return (object) $result;
    }

    /**
     * @param T $object
     *
     * @return array<non-empty-string, mixed>
     * @throws \Throwable in case of object's property is not accessible
     */
    protected function normalizeObject(object $object, Context $context): array
    {
        $result = [];

        foreach ($this->metadata->getProperties() as $meta) {
            $entrance = $context->enter($object, new ObjectPropertyEntry($meta->getName()));

            // Skip the property when not readable
            if (!$this->accessor->isReadable($object, $meta)) {
                continue;
            }

            // Assert that type is present
            $info = $meta->findTypeInfo();
            if ($info === null) {
                throw MissingFieldTypeException::createFromContext(
                    field: $meta->getName(),
                    context: $entrance,
                );
            }

            $fieldValue = $this->accessor->getValue($object, $meta);

            // Skip the property when condition is matched
            $skip = $meta->findSkipCondition();

            if ($skip !== null) {
                $nodes = $skip->getNodes();

                if ((bool) $nodes->evaluate([], ['this' => $object])) {
                    continue;
                }
            }

            $type = $info->getType();
            try {
                $result[$meta->getExportName()] = $type->cast($fieldValue, $entrance);
            } catch (FieldExceptionInterface|MappingExceptionInterface $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw InvalidFieldTypeValueException::createFromContext(
                    field: $meta->getExportName(),
                    value: $fieldValue,
                    expected: $info->getTypeStatement(),
                    object: $this->metadata->getTypeStatement($entrance),
                    context: $entrance,
                    previous: $e,
                );
            }
        }

        return $result;
    }
}
