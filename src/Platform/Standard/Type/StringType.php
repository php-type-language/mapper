<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class StringType implements TypeInterface
{
    /** @var string */
    protected const NULL_TO_STRING = '';
    /** @var string */
    protected const TRUE_TO_STRING = 'true';
    /** @var string */
    protected const FALSE_TO_STRING = 'false';
    /** @var string */
    protected const NAN_TO_STRING = 'nan';
    /** @var string */
    protected const INF_TO_STRING = 'inf';

    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value);
    }

    public function cast(mixed $value, Context $context): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            return $this->convertToString($value, $context);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }

    /**
     * @throws InvalidValueException
     */
    protected function convertToString(mixed $value, Context $context): string
    {
        return match (true) {
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
                // Non-zero float number
                \str_contains($result = (string) $value, '.') => $result,
                // Integer-like float number
                default => \number_format($value, 1, '.', ''),
            },
            // Int
            \is_int($value),
            // Stringable
            $value instanceof \Stringable => (string) $value,
            // Enum
            $value instanceof \BackedEnum => (string) $value->value,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
