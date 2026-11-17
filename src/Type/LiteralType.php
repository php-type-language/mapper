<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * One value, such as `42`, `'active'`, `true` or a case of an enum. Other
 * forms of the value are coerced by the type of the value (`'42'` for `42`)
 * unless the context is strict, the value is normalized by that type.
 *
 * @template T of int|float|string|bool|\UnitEnum
 *
 * @template-implements TypeInterface<T>
 */
final class LiteralType implements TypeInterface
{
    public function __construct(
        /**
         * @var T
         */
        public readonly int|float|string|bool|\UnitEnum $value,
        /**
         * Type of the value, such as `int` for `42`
         *
         * @var TypeInterface<int|float|string|bool|\UnitEnum>
         */
        public readonly TypeInterface $type,
        /**
         * Name of the type in messages, such as `'active'`
         *
         * @var non-empty-string
         */
        public readonly string $name,
    ) {}

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        if ($value === $this->value) {
            return true;
        }

        return $this->type->isDenormalizable($value, $context)
            && $this->type->denormalize($value, $context) === $this->value;
    }

    /**
     * @return T
     * @throws InvalidValueException in case of the value is another one
     */
    public function denormalize(mixed $value, Context $context): int|float|string|bool|\UnitEnum
    {
        if ($value === $this->value) {
            return $this->value;
        }

        if (!$this->isDenormalizable($value, $context)) {
            throw InvalidValueException::becauseInvalidType($this->name, $value);
        }

        return $this->value;
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    /**
     * @throws InvalidValueException in case of the value is another one
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        if ($value === $this->value) {
            return $this->type->normalize($value, $context);
        }

        throw InvalidValueException::becauseInvalidType($this->name, $value);
    }
}
