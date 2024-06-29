<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\LocalContext;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Registry\RegistryInterface;

/**
 * @template-extends NonDirectionalType<bool, bool>
 */
final class BoolType extends NonDirectionalType
{
    /**
     * Converts incoming value to the bool (in case of strict types is disabled).
     */
    protected function format(mixed $value, RegistryInterface $types, LocalContext $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->castToBoolIfPossible($value);
        }

        if (!\is_bool($value)) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: 'bool',
                actualValue: $value,
            );
        }

        return $value;
    }

    /**
     * A method to convert input data to a bool representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function castToBoolIfPossible(mixed $value): bool
    {
        return match (true) {
            \is_array($value) => $value !== [],
            \is_object($value) => true,
            \is_string($value) => $value !== '',
            default => (bool) $value,
        };
    }
}
