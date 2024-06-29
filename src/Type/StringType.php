<?php

declare(strict_types=1);

namespace Serafim\Mapper\Type;

use Serafim\Mapper\Context\LocalContext;
use Serafim\Mapper\Exception\Mapping\InvalidValueException;
use Serafim\Mapper\Registry\RegistryInterface;

/**
 * @template-extends NonDirectionalType<string, string>
 */
final class StringType extends NonDirectionalType
{
    /**
     * Converts incoming value to the string (in case of strict types is disabled).
     */
    protected function format(mixed $value, RegistryInterface $types, LocalContext $context): string
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->castToStringIfPossible($value);
        }

        if (!\is_string($value)) {
            throw InvalidValueException::becauseInvalidValue(
                context: $context,
                expectedType: 'string',
                actualValue: $value,
            );
        }

        return $value;
    }

    /**
     * A method to convert input data to a string representation, if possible.
     *
     * If conversion is not possible, it returns the value "as is".
     */
    private function castToStringIfPossible(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        return match (true) {
            $value instanceof \Stringable => (string) $value,
            \is_array($value),
            \is_object($value) => $value,
            $value === true => '1',
            $value === false => '0',
            default => (string) $value,
        };
    }
}
