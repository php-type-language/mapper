<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\MissingRequiredFieldException;
use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

/**
 * @template T of object
 * @template-implements TypeInterface<T, object|array<non-empty-string, mixed>>
 */
final class ObjectType implements TypeInterface
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        private readonly ClassMetadata $metadata,
    ) {}

    /**
     * @throws InvalidValueException
     * @throws \ReflectionException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: $this->metadata->getTypeStatement(false),
                actualValue: $value,
            );
        }

        return $this->normalizeObject($value, $types, $context);
    }

    /**
     * @param T $object
     *
     * @return object|array<non-empty-string, mixed>
     * @throws \ReflectionException
     */
    private function normalizeObject(object $object, RegistryInterface $types, LocalContext $context): object|array
    {
        $result = [];
        $reflection = $this->metadata->getReflection();

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter($meta->getName());

            // Fetch property value from object
            $propertyValue = $this->getValue(
                property: $meta->getReflection($reflection),
                object: $object,
            );

            // Convert inherited property value if type is defined
            if (($type = $meta->getType()) !== null) {
                $propertyValue = $type->normalize($propertyValue, $types, $context);
            }

            $result[$meta->getExportName()] = $propertyValue;

            $context->leave();
        }

        if ($context->isObjectsAsArrays()) {
            return $result;
        }

        return (object) $result;
    }

    private function getValue(\ReflectionProperty $property, object $object): mixed
    {
        return $property->getValue($object);
    }

    /**
     * @throws InvalidValueException
     * @throws MissingRequiredFieldException
     * @throws \ReflectionException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): object
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: $this->metadata->getName(),
                actualValue: $value,
            );
        }

        return $this->denormalizeObject($value, $types, $context);
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @return T
     * @throws MissingRequiredFieldException
     * @throws \ReflectionException
     */
    private function denormalizeObject(array $value, RegistryInterface $types, LocalContext $context): object
    {
        $object = $this->newInstance();
        $reflection = $this->metadata->getReflection();

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter($meta->getExportName());

            $property = $meta->getReflection($reflection);

            // In case of value has been passed
            if (\array_key_exists($meta->getExportName(), $value)) {
                $propertyValue = $value[$meta->getExportName()];

                // Convert inherited property value if type is defined
                if (($type = $meta->getType()) !== null) {
                    $propertyValue = $type->denormalize($propertyValue, $types, $context);
                }

                $this->setValue($property, $object, $propertyValue);
                $context->leave();
                continue;
            }

            // In case of property has default argument
            if ($meta->hasDefaultValue()) {
                $this->setValue($property, $object, $meta->getDefaultValue());
                $context->leave();
                continue;
            }

            throw MissingRequiredFieldException::becauseFieldIsMissing(
                context: $context,
                expectedType: $this->metadata->getTypeStatement(true),
                field: $meta->getExportName(),
            );
        }

        return $object;
    }

    private function setValue(\ReflectionProperty $property, object $object, mixed $value): void
    {
        $property->setValue($object, $value);
    }

    /**
     * @return T
     * @throws \ReflectionException
     */
    private function newInstance(): object
    {
        $reflection = $this->metadata->getReflection();

        return $reflection->newInstanceWithoutConstructor();
    }
}
