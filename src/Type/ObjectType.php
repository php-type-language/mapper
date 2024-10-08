<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\MissingFieldValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Type\Context\LocalContext;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
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
     */
    public function normalize(mixed $value, RepositoryInterface $types, LocalContext $context): object|array
    {
        $className = $this->metadata->getName();

        if (!$value instanceof $className) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->getTypeStatement($context),
                context: $context,
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
    private function normalizeObject(object $object, RepositoryInterface $types, LocalContext $context): object|array
    {
        $result = [];
        $reflection = new \ReflectionClass($this->metadata->getName());

        $context->enter(new ObjectEntry($this->metadata->getName()));

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter(new ObjectPropertyEntry($meta->getName()));

            // Fetch property value from object
            $propertyValue = $this->getValue(
                property: $reflection->getProperty($meta->getName()),
                object: $object,
            );

            $type = $meta->getType();

            if ($type === null) {
                continue;
            }

            $result[$meta->getExportName()] = $type->cast($propertyValue, $types, $context);

            $context->leave();
        }

        $context->leave();

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
     * @throws MissingFieldValueException
     * @throws \ReflectionException
     */
    public function denormalize(mixed $value, RepositoryInterface $types, LocalContext $context): object
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueException::becauseInvalidValueGiven(
                value: $value,
                expected: $this->metadata->getName(),
                context: $context,
            );
        }

        return $this->denormalizeObject($value, $types, $context);
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @return T
     * @throws MissingFieldValueException
     * @throws \ReflectionException
     */
    private function denormalizeObject(array $value, RepositoryInterface $types, LocalContext $context): object
    {
        $reflection = new \ReflectionClass($this->metadata->getName());
        $object = $reflection->newInstanceWithoutConstructor();

        $context->enter(new ObjectEntry($this->metadata->getName()));

        foreach ($this->metadata->getProperties() as $meta) {
            $context->enter(new ObjectPropertyEntry($meta->getExportName()));

            $property = $reflection->getProperty($meta->getName());

            // In case of value has been passed
            if (\array_key_exists($meta->getExportName(), $value)) {
                $type = $meta->getType();

                if ($type === null) {
                    continue;
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

            throw MissingFieldValueException::becausePropertyValueRequired(
                field: $meta->getExportName(),
                expected: $this->getTypeStatement($context),
                context: $context,
            );
        }

        $context->leave();

        return $object;
    }

    private function setValue(\ReflectionProperty $property, object $object, mixed $value): void
    {
        $property->setValue($object, $value);
    }
}
