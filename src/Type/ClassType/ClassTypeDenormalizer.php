<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Exception\Mapping\FieldExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidFieldTypeValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueMappingException;
use TypeLang\Mapper\Exception\Mapping\MappingExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\MissingFieldTypeException;
use TypeLang\Mapper\Exception\Mapping\MissingFieldValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMapMetadata;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Type\ClassType\ClassInstantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Type\ClassType\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template T of object
 */
class ClassTypeDenormalizer implements TypeInterface
{
    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        protected readonly ClassMetadata $metadata,
        protected readonly PropertyAccessorInterface $accessor,
        protected readonly ClassInstantiatorInterface $instantiator,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return \is_object($value) || \is_array($value);
    }

    /**
     * @return T|mixed
     * @throws InvalidValueMappingException
     * @throws MissingFieldValueException
     * @throws RuntimeExceptionInterface
     * @throws TypeNotFoundException
     * @throws \Throwable
     */
    public function cast(mixed $value, Context $context): mixed
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueMappingException::createFromContext(
                value: $value,
                expected: $this->metadata->getTypeStatement($context),
                context: $context,
            );
        }

        $discriminator = $this->metadata->findDiscriminator();

        if ($discriminator !== null) {
            return $this->castOverDiscriminator($discriminator, $value, $context);
        }

        $entrance = $context->enter($value, new ObjectEntry($this->metadata->getName()));

        $instance = $this->instantiator->instantiate($this->metadata);

        $this->denormalizeObject($value, $instance, $entrance);

        return $instance;
    }

    /**
     * @param array<array-key, mixed> $value
     * @throws MissingFieldValueException
     * @throws \Throwable
     * @throws TypeNotFoundException
     * @throws RuntimeExceptionInterface
     */
    private function castOverDiscriminator(DiscriminatorMapMetadata $map, array $value, Context $context): mixed
    {
        $discriminatorValue = $value[$map->getField()] ?? null;

        // Invalid discriminator field type
        if (!\is_string($discriminatorValue)) {
            throw MissingFieldValueException::createFromContext(
                expected: $this->metadata->getTypeStatement($context),
                field: $map->getField(),
                context: $context,
            );
        }

        $discriminatorDefinition = $map->findType($discriminatorValue);

        // Invalid discriminator type
        if ($discriminatorDefinition === null) {
            throw MissingFieldValueException::createFromContext(
                expected: $this->metadata->getTypeStatement($context),
                field: $map->getField(),
                context: $context,
            );
        }

        $discriminatorType = $context->getTypeByDefinition($discriminatorDefinition);

        return $discriminatorType->cast($value, $context);
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @throws MissingFieldValueException
     * @throws \Throwable in case of object's property is not accessible
     */
    private function denormalizeObject(array $value, object $object, Context $context): void
    {
        foreach ($this->metadata->getProperties() as $meta) {
            $entrance = $context->enter($value, new ObjectPropertyEntry($meta->getExportName()));

            // Skip the property when not writable
            if (!$this->accessor->isWritable($object, $meta)) {
                continue;
            }

            switch (true) {
                // In case of value has been passed
                case \array_key_exists($meta->getExportName(), $value):
                    // Assert that type is present
                    $info = $meta->findTypeInfo();

                    if ($info === null) {
                        throw MissingFieldTypeException::createFromContext(
                            field: $meta->getExportName(),
                            context: $entrance,
                        );
                    }

                    $fieldValue = $value[$meta->getExportName()];
                    $type = $info->getType();

                    try {
                        $propertyValue = $type->cast($fieldValue, $entrance);
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
                    break;

                    // In case of property has default argument
                case $meta->hasDefaultValue():
                    $propertyValue = $meta->findDefaultValue();
                    break;

                default:
                    throw MissingFieldValueException::createFromContext(
                        expected: $this->metadata->getTypeStatement($entrance),
                        field: $meta->getExportName(),
                        context: $entrance,
                    );
            }

            $this->accessor->setValue($object, $meta, $propertyValue);
        }
    }
}
