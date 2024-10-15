<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class BackedEnumType extends AsymmetricType
{
    /**
     * @param class-string<\BackedEnum> $class
     */
    public function __construct(
        private readonly string $class,
        private readonly TypeInterface $type,
    ) {}

    protected function isNormalizable(mixed $value, LocalContext $context): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * Converts enum case (of {@see \BackedEnum}) objects to their
     * scalar representation.
     *
     * @throws InvalidValueException
     */
    public function normalize(mixed $value, LocalContext $context): int|string
    {
        if (!$value instanceof $this->class) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $value->value;
    }

    /**
     * @throws \Throwable
     * @throws RuntimeExceptionInterface
     */
    protected function isDenormalizable(mixed $value, LocalContext $context): bool
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
    public function denormalize(mixed $value, LocalContext $context): \BackedEnum
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
