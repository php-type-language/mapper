<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `int`: an integer as is, its other forms are coerced on denormalization
 * unless the context is strict.
 *
 * @template-implements TypeInterface<int>
 */
final class IntType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return \is_int($value)
            || (!$context->config->isStrict && $context->coercers->int->coerce($value) !== null);
    }

    /**
     * @throws InvalidValueException in case of the value is not an integer
     */
    public function denormalize(mixed $value, Context $context): int
    {
        if (\is_int($value)) {
            return $value;
        }

        if (!$context->config->isStrict) {
            return $context->coercers->int->coerce($value)
                ?? throw InvalidValueException::becauseInvalidType('int', $value);
        }

        throw InvalidValueException::becauseInvalidType('int', $value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return \is_int($value);
    }

    /**
     * @throws InvalidValueException in case of the value is not an integer
     */
    public function normalize(mixed $value, Context $context): int
    {
        if (\is_int($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidType('int', $value);
    }
}
