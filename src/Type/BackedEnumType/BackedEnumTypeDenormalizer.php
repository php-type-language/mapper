<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\BackedEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

class BackedEnumTypeDenormalizer implements TypeInterface
{
    /**
     * @param class-string<\BackedEnum> $class
     */
    public function __construct(
        protected readonly string $class,
        protected readonly TypeInterface $type,
    ) {}

    /**
     * @throws \Throwable
     * @throws RuntimeExceptionInterface
     */
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

    /**
     * Converts a scalar representation of an enum to an enum case object.
     *
     * @throws InvalidValueException
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
     */
    public function cast(mixed $value, Context $context): \BackedEnum
    {
        $denormalized = $this->type->cast($value, $context);

        if (!\is_string($denormalized) && !\is_int($denormalized)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

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
