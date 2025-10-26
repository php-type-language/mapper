<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template TEnum of \BackedEnum = \BackedEnum
 * @template-implements TypeInterface<TEnum>
 */
class BackedEnumFromScalarType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string<TEnum>
         */
        protected readonly string $class,
        /**
         * @var TypeInterface<string|int>
         */
        protected readonly TypeInterface $type,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        $isSupportsType = $this->type->match($value, $context);

        if (!$isSupportsType) {
            return false;
        }

        /** @var int|string $denormalized */
        $denormalized = $this->type->cast($value, $context);

        try {
            return ($this->class)::tryFrom($denormalized) !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    public function cast(mixed $value, Context $context): \BackedEnum
    {
        $denormalized = $this->type->cast($value, $context);

        try {
            $case = $this->class::tryFrom($denormalized);
        } catch (\TypeError $e) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
                previous: $e,
            );
        }

        return $case ?? throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
