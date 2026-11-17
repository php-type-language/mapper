<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `float`: a float as is, including `INF` and `NAN`. An exactly
 * representable integer is accepted in the strict context too. Numeric
 * strings are coerced on denormalization unless the context is strict.
 *
 * @template-implements TypeInterface<float>
 */
final class FloatType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return \is_float($value)
            || ((\is_int($value) || !$context->config->isStrict) && $context->coercers->float->coerce($value) !== null);
    }

    /**
     * @throws InvalidValueException in case of the value is not a float
     */
    public function denormalize(mixed $value, Context $context): float
    {
        if (\is_float($value)) {
            return $value;
        }

        if (\is_int($value) || !$context->config->isStrict) {
            return $context->coercers->float->coerce($value)
                ?? throw InvalidValueException::becauseInvalidType('float', $value);
        }

        throw InvalidValueException::becauseInvalidType('float', $value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return \is_float($value);
    }

    /**
     * @throws InvalidValueException in case of the value is not a float
     */
    public function normalize(mixed $value, Context $context): float
    {
        if (\is_float($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidType('float', $value);
    }
}
