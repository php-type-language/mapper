<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\InvalidValueException;

/**
 * `A|B|...`: the value by the first type accepting it.
 *
 * On denormalization a type accepting the value exactly wins over a type
 * accepting it by coercion: the types are checked in the strict context
 * first, then in the given one in the coercion order.
 *
 * ```
 * // int|string
 * $union->denormalize('42', $context); // '42'
 * $union->denormalize(42.0, $context); // 42
 * ```
 *
 * @template T
 *
 * @template-implements TypeInterface<T>
 */
final class UnionType implements TypeInterface
{
    /**
     * @var non-empty-list<TypeInterface<T>>
     */
    private readonly array $coercionOrder;

    /**
     * @param non-empty-list<TypeInterface<T>>|null $coercionOrder the same
     *        types in the order they are tried with coercion, the declared
     *        order in case of {@see null}
     */
    public function __construct(
        /**
         * Types in the declared order, checked in it for an exact match and
         * on normalization
         *
         * @var non-empty-list<TypeInterface<T>>
         */
        public readonly array $types,
        /**
         * Canonical description of the union, for messages
         *
         * @var non-empty-string
         */
        public readonly string $name,
        ?array $coercionOrder = null,
    ) {
        $this->coercionOrder = $coercionOrder ?? $types;
    }

    public function isDenormalizable(mixed $value, Context $context): bool
    {
        foreach ($this->types as $type) {
            if ($type->isDenormalizable($value, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws InvalidValueException in case of no type accepts the value
     */
    public function denormalize(mixed $value, Context $context): mixed
    {
        $strict = $context->withStrictTypes(true);

        foreach ($this->types as $type) {
            if ($type->isDenormalizable($value, $strict)) {
                return $type->denormalize($value, $strict);
            }
        }

        if ($strict !== $context) {
            foreach ($this->coercionOrder as $type) {
                if ($type->isDenormalizable($value, $context)) {
                    return $type->denormalize($value, $context);
                }
            }
        }

        throw InvalidValueException::becauseInvalidType($this->name, $value);
    }

    public function isNormalizable(mixed $value, Context $context): bool
    {
        foreach ($this->types as $type) {
            if ($type->isNormalizable($value, $context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws InvalidValueException in case of no type accepts the value
     */
    public function normalize(mixed $value, Context $context): mixed
    {
        foreach ($this->types as $type) {
            if ($type->isNormalizable($value, $context)) {
                return $type->normalize($value, $context);
            }
        }

        throw InvalidValueException::becauseInvalidType($this->name, $value);
    }
}
