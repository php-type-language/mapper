<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class StringType implements TypeInterface
{
    protected const NULL_TO_STRING  = '';
    protected const TRUE_TO_STRING  = 'true';
    protected const FALSE_TO_STRING = 'false';
    protected const NAN_TO_STRING   = 'nan';
    protected const INF_TO_STRING   = 'inf';

    public function match(mixed $value, Context $context): bool
    {
        return \is_string($value);
    }

    /**
     * Converts incoming value to the string (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, Context $context): string
    {
        if (\is_string($value)) {
            /** @var string */
            return $value;
        }

        if (!$context->isStrictTypesEnabled()) {
            return $this->tryCast($value, $context);
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }

    /**
     * @throws InvalidValueException
     */
    private function tryCast(mixed $value, Context $context): string
    {
        return match (true) {
            $value === null => static::NULL_TO_STRING,
            $value === true => static::TRUE_TO_STRING,
            $value === false => static::FALSE_TO_STRING,
            \is_float($value) => match (true) {
                \is_nan($value) => static::NAN_TO_STRING,
                \is_infinite($value) => ($value >= 0 ? '' : '-') . static::INF_TO_STRING,
                default => (string) $value,
            },
            \is_int($value),
            $value instanceof \Stringable => (string) $value,
            $value instanceof \BackedEnum => (string) $value->value,
            default => throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            ),
        };
    }
}
