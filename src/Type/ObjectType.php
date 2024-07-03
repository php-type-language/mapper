<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Context\Path\ObjectPropertyEntry;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\MissingRequiredFieldException;
use TypeLang\Mapper\Exception\TypeRequiredException;
use TypeLang\Mapper\Meta\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template T of object
 */
final class ObjectType extends AsymmetricLogicalType
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        private readonly ClassMetadata $metadata,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return $this->metadata->getTypeStatement($context);
    }

    protected function supportsNormalization(mixed $value, LocalContext $context): bool
    {
        $class = $this->metadata->getName();

        return $value instanceof $class;
    }

    /**
     * @return object|array<non-empty-string, mixed>
     * @throws InvalidValueException
     * @throws \ReflectionException
     * @throws TypeRequiredException
     */
    public function normalize(mixed $value, RegistryInterface $types, LocalContext $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueException::becauseInvalidValueGiven(
                context: $context,
                expectedType: $this->getTypeStatement($context),
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
     * @throws TypeRequiredException
     */
    private function normalizeObject(object $object, RegistryInterface $types, LocalContext $context): object|array
    {
        $result = [];
        $reflection = $this->metadata->getReflection();

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter(new ObjectPropertyEntry($meta->getName()));

            // Fetch property value from object
            $propertyValue = $this->getValue(
                property: $meta->getReflection($reflection),
                object: $object,
            );

            $type = $meta->getType();

            if ($type === null) {
                throw TypeRequiredException::fromInvalidFieldType(
                    class: $this->metadata->getName(),
                    field: $meta->getName(),
                );
            }

            $result[$meta->getExportName()] = $type->cast($propertyValue, $types, $context);

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

    protected function supportsDenormalization(mixed $value, LocalContext $context): bool
    {
        return \is_object($value) || \is_array($value);
    }

    /**
     * @return T
     * @throws InvalidValueException
     * @throws MissingRequiredFieldException
     * @throws TypeRequiredException
     * @throws \ReflectionException
     */
    public function denormalize(mixed $value, RegistryInterface $types, LocalContext $context): object
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
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
     * @throws TypeRequiredException
     */
    private function denormalizeObject(array $value, RegistryInterface $types, LocalContext $context): object
    {
        $object = $this->newInstance();
        $reflection = $this->metadata->getReflection();

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter(new ObjectPropertyEntry($meta->getExportName()));

            $property = $meta->getReflection($reflection);

            // In case of value has been passed
            if (\array_key_exists($meta->getExportName(), $value)) {
                $type = $meta->getType();

                if ($type === null) {
                    throw TypeRequiredException::fromInvalidFieldType(
                        class: $this->metadata->getName(),
                        field: $meta->getName(),
                    );
                }

                $propertyValue = $type->cast($value[$meta->getExportName()], $types, $context);

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
                expectedType: $this->getTypeStatement($context),
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
