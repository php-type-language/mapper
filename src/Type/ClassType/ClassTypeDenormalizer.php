<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Exception\Mapping\FinalExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueOfTypeException;
use TypeLang\Mapper\Exception\Mapping\MissingRequiredObjectFieldException;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;
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
     * @throws MissingRequiredObjectFieldException in case the required field is missing
     * @throws InvalidObjectValueException in case the value of a certain field is incorrect
     * @throws \Throwable in case of object's property is not accessible
     */
    public function cast(mixed $value, Context $context): mixed
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueOfTypeException::createFromContext(
                expected: $this->metadata->getTypeStatement($context),
                value: $value,
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
     *
     * @throws MissingRequiredObjectFieldException in case the required discriminator field is missing
     * @throws InvalidObjectValueException in case the discriminator field contains invalid value
     * @throws RuntimeException in case of mapped type casting error occurs
     * @throws \Throwable in case of internal error occurs
     */
    private function castOverDiscriminator(DiscriminatorMapMetadata $map, array $value, Context $context): mixed
    {
        $field = $map->getField();

        // In case of discriminator field is missing
        if (!\array_key_exists($field, $value)) {
            throw MissingRequiredObjectFieldException::createFromContext(
                field: $field,
                expected: $map->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        $element = $value[$field];

        // In case of discriminator field is not a string
        if (!\is_string($element)) {
            throw InvalidObjectValueException::createFromContext(
                element: $element,
                field: $field,
                expected: $map->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        $mapping = $map->findType($element);

        // In case of discriminator value is not found
        if ($mapping === null) {
            throw InvalidObjectValueException::createFromContext(
                element: $element,
                field: $field,
                expected: $map->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        $mappingType = $mapping->getType();

        return $mappingType->cast($value, $context);
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @throws MissingRequiredObjectFieldException in case the required field is missing
     * @throws InvalidObjectValueException in case the value of a certain field is incorrect
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
                    $element = $value[$meta->getExportName()];

                    // Fetch field type
                    $info = $meta->findTypeInfo();
                    $type = $info !== null ? $info->getType() : $context->getTypeByDefinition('mixed');

                    try {
                        $element = $type->cast($element, $entrance);
                    } catch (FinalExceptionInterface $e) {
                        throw $e;
                    } catch (\Throwable $e) {
                        throw InvalidObjectValueException::createFromContext(
                            element: $element,
                            field: $meta->getExportName(),
                            expected: $meta->getTypeStatement($entrance),
                            value: $value,
                            context: $entrance,
                            previous: $e,
                        );
                    }
                    break;

                    // In case of property has default argument
                case $meta->hasDefaultValue():
                    $element = $meta->findDefaultValue();
                    break;

                default:
                    throw MissingRequiredObjectFieldException::createFromContext(
                        field: $meta->getExportName(),
                        expected: $meta->getTypeStatement($entrance),
                        value: $value,
                        context: $entrance,
                    );
            }

            $this->accessor->setValue($object, $meta, $element);
        }
    }
}
