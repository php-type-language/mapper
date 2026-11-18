<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `null` in both directions.
 *
 * @template-implements TypeInterface<null>
 */
final class NullType implements TypeInterface
{
    public function isDenormalizable(mixed $value, Context $context): bool
    {
        return $value === null;
    }

    /**
     * @throws InvalidValueException in case of the value is not null
     */
    public function denormalize(mixed $value, Context $context): mixed
    {
        if ($value === null) {
            return null;
        }

        throw InvalidValueException::becauseInvalidType($value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $value === null;
    }

    /**
     * @throws InvalidValueException in case of the value is not null
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        return $this->denormalize($value, $context);
    }
}
