<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\ClassType;

use TypeLang\Mapper\Exception\Mapping\InvalidObjectValueException;
use TypeLang\Mapper\Exception\Mapping\MissingRequiredObjectFieldException;
use TypeLang\Mapper\Exception\Mapping\RuntimeException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

final class DiscriminatorTypeSelector
{
    /**
     * @param array<array-key, mixed> $value
     *
     * @throws MissingRequiredObjectFieldException in case the required discriminator field is missing
     * @throws InvalidObjectValueException in case the discriminator field contains invalid value
     * @throws RuntimeException in case of mapped type casting error occurs
     * @throws \Throwable in case of internal error occurs
     */
    public function select(ClassMetadata $meta, mixed $value, Context $context): ?TypeInterface
    {
        $map = $meta->discriminator;

        if ($map === null) {
            return null;
        }

        // Default mapping type
        $default = $map->default?->type;
        $field = $map->field;

        // In case of discriminator field is missing
        if (!\array_key_exists($field, $value)) {
            // In case of default type is present
            if ($default !== null) {
                return $default;
            }

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
            // In case of default type is present
            if ($default !== null) {
                return $default;
            }

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
            // In case of default type is present
            if ($default !== null) {
                return $default;
            }

            throw InvalidObjectValueException::createFromContext(
                element: $element,
                field: $field,
                expected: $map->getTypeStatement(),
                value: $value,
                context: $context,
            );
        }

        return $mapping->type;
    }
}
