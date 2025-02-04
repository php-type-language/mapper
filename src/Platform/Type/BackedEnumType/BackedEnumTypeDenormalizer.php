<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Type\BackedEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

class BackedEnumTypeDenormalizer implements TypeInterface
{
    /**
     * @param class-string<\BackedEnum> $class
     */
    public function __construct(
        protected readonly string $class,
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
