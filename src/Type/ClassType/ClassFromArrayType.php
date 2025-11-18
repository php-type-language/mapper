<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Context\Path\Entry\ObjectEntry;
use TypeLang\Mapper\Context\Path\Entry\ObjectPropertyEntry;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Runtime\InvalidValueOfTypeException;
use TypeLang\Mapper\Exception\Runtime\MissingRequiredObjectFieldException;
use TypeLang\Mapper\Exception\Runtime\NonInstantiatableException;
use TypeLang\Mapper\Exception\Runtime\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TObject of object = object
 *
 * @template-implements TypeInterface<TObject>
 */
class ClassFromArrayType implements TypeInterface
{
    /**
     * @var DiscriminatorTypeSelector<TObject>
     */
    protected readonly DiscriminatorTypeSelector $discriminator;

    public function __construct(
        /**
         * @var ClassMetadata<TObject>
         */
        protected readonly ClassMetadata $metadata,
        protected readonly PropertyAccessorInterface $accessor,
        protected readonly ClassInstantiatorInterface $instantiator,
    ) {
        $this->discriminator = new DiscriminatorTypeSelector($metadata);
    }

    public function match(mixed $value, RuntimeContext $context): bool
    {
        return (\is_array($value) || \is_object($value))
            && $this->matchRequiredProperties((array) $value, $context);
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private function matchRequiredProperties(array $payload, RuntimeContext $context): bool
    {
        foreach ($this->metadata->properties as $meta) {
            // Match property for existence
            if (!\array_key_exists($meta->alias, $payload)) {
                // Skip all properties with defaults
                if ($meta->default !== null) {
                    continue;
                }

                return false;
            }

            // Assert valid type
            if (!$meta->write->type->match($payload[$meta->alias], $context)) {
                return false;
            }
        }

        return true;
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        if (\is_object($value)) {
            $value = (array) $value;
        }

        if (!\is_array($value)) {
            throw InvalidValueOfTypeException::createFromContext(
                expected: $this->metadata->getTypeStatement($context, read: false),
                context: $context,
            );
        }

        $discriminator = $this->discriminator->select($value, $context);

        if ($discriminator !== null) {
            return $discriminator->cast($value, $context);
        }

        $entrance = $context->enter($value, new ObjectEntry($this->metadata->name));

        try {
            $instance = $this->instantiator->instantiate($this->metadata->name);
        } catch (\Throwable $e) {
            throw NonInstantiatableException::createFromContext(
                expected: $this->metadata->getTypeStatement($context, read: false),
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
    private function denormalizeObject(array $value, object $object, RuntimeContext $context): void
    {
        foreach ($this->metadata->properties as $meta) {
            $entrance = $context->enter(
                value: (object) $value,
                entry: new ObjectPropertyEntry($meta->alias),
                config: $context->withStrictTypes($meta->write->strict)
            );

            // Skip the property when not writable
            if (!$this->accessor->isWritable($object, $meta->name)) {
                continue;
            }

            switch (true) {
                // In case of value has been passed
                case \array_key_exists($meta->alias, $value):
                    $element = $value[$meta->alias];

                    try {
                        $element = $meta->write->type->cast($element, $entrance);
                    } catch (NotInterceptableExceptionInterface $e) {
                        throw $e;
                    } catch (\Throwable $e) {
                        $exception = InvalidObjectValueException::createFromContext(
                            element: $element,
                            field: $meta->alias,
                            expected: $meta->getTypeStatement($entrance, read: false),
                            context: $entrance,
                            previous: $e,
                        );

                        if ($meta->typeErrorMessage !== null) {
                            $exception->updateMessage($meta->typeErrorMessage);
                        }

                        throw $exception;
                    }
                    break;

                    // In case of property has default argument
                case $meta->default !== null:
                    $element = $meta->default->value;
                    break;

                default:
                    $exception = MissingRequiredObjectFieldException::createFromContext(
                        field: $meta->alias,
                        expected: $meta->getTypeStatement($entrance, read: false),
                        context: $entrance,
                    );

                    if ($meta->undefinedErrorMessage !== null) {
                        $exception->updateMessage($meta->undefinedErrorMessage);
                    }

                    throw $exception;
            }

            $this->accessor->setValue($object, $meta->name, $element);
        }
    }
}
