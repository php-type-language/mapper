<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `string`: a string as is, its other forms are coerced on denormalization
 * unless the context is strict.
 *
 * @template-implements TypeInterface<string>
 */
final class StringType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return \is_string($value)
            || (!$context->config->isStrict && $context->coercers->string->coerce($value) !== null);
    }

    /**
     * @throws InvalidValueException in case of the value is not a string
     */
    public function denormalize(mixed $value, Context $context): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (!$context->config->isStrict) {
            return $context->coercers->string->coerce($value)
                ?? throw InvalidValueException::becauseInvalidType('string', $value);
        }

        throw InvalidValueException::becauseInvalidType('string', $value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return \is_string($value);
    }

    /**
     * @throws InvalidValueException in case of the value is not a string
     */
    public function normalize(mixed $value, Context $context): string
    {
        if (\is_string($value)) {
            return $value;
        }

        throw InvalidValueException::becauseInvalidType('string', $value);
    }
}
