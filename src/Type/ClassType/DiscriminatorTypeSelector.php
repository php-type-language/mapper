<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Runtime\MissingRequiredObjectFieldException;
use TypeLang\Mapper\Exception\Runtime\RuntimeException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template TObject of object = object
 */
final class DiscriminatorTypeSelector
{
    public function __construct(
        /**
         * @var ClassMetadata<TObject>
         */
        private readonly ClassMetadata $meta,
    ) {}

    /**
     * @param array<array-key, mixed> $value
     *
     * @return TypeInterface<TObject>|null
     * @throws MissingRequiredObjectFieldException in case the required discriminator field is missing
     * @throws InvalidObjectValueException in case the discriminator field contains invalid value
     * @throws RuntimeException in case of mapped type casting error occurs
     * @throws \Throwable in case of internal error occurs
     */
    public function select(mixed $value, Context $context): ?TypeInterface
    {
        $discriminator = $this->meta->discriminator;

        if ($discriminator === null) {
            return null;
        }

        // Default mapping type
        $default = $discriminator->default?->type;
        $field = $discriminator->field;

        // In case of discriminator field is missing
        if (!\array_key_exists($field, $value)) {
            // In case of default type is present
            if ($default !== null) {
                return $default;
            }

            throw MissingRequiredObjectFieldException::createFromContext(
                field: $field,
                expected: $discriminator->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        $element = $value[$field];

        // In case of discriminator field is not a string
        if (!\is_string($element)) {
            // In case of default type is present
            if ($default !== null) {
                return $default;
            }

            throw InvalidObjectValueException::createFromContext(
                element: $element,
                field: $field,
                expected: $discriminator->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        $mapping = $discriminator->map[$element] ?? null;

        // In case of discriminator value is not found
        if ($mapping === null) {
            // In case of default type is present
            if ($default !== null) {
                return $default;
            }

            throw InvalidObjectValueException::createFromContext(
                element: $element,
                field: $field,
                expected: $discriminator->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        return $mapping->type;
    }
}
