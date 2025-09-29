<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Exception\Mapping\FinalExceptionInterface;
use TypeLang\Mapper\Exception\Mapping\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueOfTypeException;
use TypeLang\Mapper\Exception\Mapping\MissingRequiredObjectFieldException;
use TypeLang\Mapper\Exception\Mapping\NonInstantiatableException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Runtime\ClassInstantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Runtime\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Runtime\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template T of object
 */
class ClassTypeDenormalizer implements TypeInterface
{
    protected readonly DiscriminatorTypeSelector $discriminator;

    /**
     * @param ClassMetadata<T> $metadata
     */
    public function __construct(
        protected readonly ClassMetadata $metadata,
        protected readonly PropertyAccessorInterface $accessor,
        protected readonly ClassInstantiatorInterface $instantiator,
    ) {
        $this->discriminator = new DiscriminatorTypeSelector();
    }

    public function match(mixed $value, Context $context): bool
    {
        return (\is_array($value) || \is_object($value))
            && $this->matchRequiredProperties((array) $value, $context);
    }

    /**
     * @throws \Throwable
     */
    private function getPropertyType(PropertyMetadata $meta, Context $context): TypeInterface
    {
        // Fetch field type
        $info = $meta->type;

        if ($info === null) {
            return $context->getTypeByDefinition('mixed');
        }

        return $info->type;
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private function matchRequiredProperties(array $payload, Context $context): bool
    {
        foreach ($this->metadata->getProperties() as $meta) {
            // Match property for existence
            if (!\array_key_exists($meta->alias, $payload)) {
                // Skip all properties with defaults
                if ($meta->hasDefaultValue()) {
                    continue;
                }

                return false;
            }

            // Fetch field value and type
            try {
                $value = $payload[$meta->alias];
                $type = $this->getPropertyType($meta, $context);
            } catch (\Throwable) {
                return false;
            }

            // Assert valid type
            if (!$type->match($value, $context)) {
                return false;
            }
        }

        return true;
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

        $discriminator = $this->discriminator->select($this->metadata, $value, $context);

        if ($discriminator !== null) {
            return $discriminator->cast($value, $context);
        }

        $entrance = $context->enter($value, new ObjectEntry($this->metadata->name));

        try {
            $instance = $this->instantiator->instantiate($this->metadata->name);
        } catch (\Throwable $e) {
            throw NonInstantiatableException::createFromContext(
                expected: $this->metadata->getTypeStatement($context),
                class: $this->metadata->name,
                context: $context,
                previous: $e,
            );
        }

        $this->denormalizeObject($value, $instance, $entrance);

        return $instance;
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
            $entrance = $context->enter($value, new ObjectPropertyEntry($meta->alias));

            // Skip the property when not writable
            if (!$this->accessor->isWritable($object, $meta->name)) {
                continue;
            }

            switch (true) {
                // In case of value has been passed
                case \array_key_exists($meta->alias, $value):
                    $element = $value[$meta->alias];
                    $type = $this->getPropertyType($meta, $context);

                    try {
                        $element = $type->cast($element, $entrance);
                    } catch (FinalExceptionInterface $e) {
                        throw $e;
                    } catch (\Throwable $e) {
                        throw InvalidObjectValueException::createFromContext(
                            element: $element,
                            field: $meta->alias,
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
                        field: $meta->alias,
                        expected: $meta->getTypeStatement($entrance),
                        value: $value,
                        context: $entrance,
                    );
            }

            $this->accessor->setValue($object, $meta->name, $element);
        }
    }
}
