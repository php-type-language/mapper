<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Coercer;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeCoercerInterface<string>
 */
class StringTypeCoercer implements TypeCoercerInterface
{
    /** @var string */
    public const NULL_TO_STRING = '';
    /** @var string */
    public const TRUE_TO_STRING = 'true';
    /** @var string */
    public const FALSE_TO_STRING = 'false';
    /** @var string */
    public const NAN_TO_STRING = 'nan';
    /** @var string */
    public const INF_TO_STRING = 'inf';

    /**
     * @throws InvalidValueException
     */
    public function coerce(mixed $value, Context $context): string
    {
        return match (true) {
            // string
            \is_string($value) => $value,
            // Null
            $value === null => static::NULL_TO_STRING,
            // True
            $value === true => static::TRUE_TO_STRING,
            // False
            $value === false => static::FALSE_TO_STRING,
            // Float
            \is_float($value) => match (true) {
                // NaN
                \is_nan($value) => static::NAN_TO_STRING,
                // Infinity
                $value === \INF => static::INF_TO_STRING,
                $value === -\INF => '-' . static::INF_TO_STRING,
                // Other floating point values
                default => \str_ends_with(
                    haystack: $formatted = \rtrim(\sprintf('%f', $value), '0'),
                    needle: '.',
                ) ? $formatted . '0' : $formatted,
            },
            // Int
            \is_int($value),
            // Stringable
            $value instanceof \Stringable => (string) $value,
            \is_resource($value) => \get_resource_type($value),
            // Enum
            $value instanceof \BackedEnum => (string) $value->value,
            $value instanceof \UnitEnum => $value->name,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
