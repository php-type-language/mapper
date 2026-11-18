<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `bool`: a boolean as is, its other forms (`1`, `'true'`, `'off'`) are
 * coerced on denormalization unless the context is strict.
 *
 * @template-implements TypeInterface<bool>
 */
final class BoolType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return \is_bool($value)
            || (!$context->config->strict && $context->coercers->bool->coerce($value) !== null);
    }

    /**
     * @throws InvalidValueException in case of the value is not a boolean
     */
    public function denormalize(mixed $value, Context $context): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (!$context->config->strict) {
            return $context->coercers->bool->coerce($value)
                ?? throw InvalidValueException::becauseInvalidType($value);
        }

        throw InvalidValueException::becauseInvalidType($value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return \is_bool($value);
    }

    /**
     * @throws InvalidValueException in case of the value is not a boolean
     */
    public function normalize(mixed $value, Context $context): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidType($value);
    }
}
