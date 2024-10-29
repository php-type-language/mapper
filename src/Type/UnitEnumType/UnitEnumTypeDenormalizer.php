<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\UnitEnumType;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Exception\Mapping\RuntimeExceptionInterface;
use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Type\TypeInterface;

class UnitEnumTypeDenormalizer implements TypeInterface
{
    /**
     * @param class-string<\UnitEnum> $class
     * @param non-empty-list<non-empty-string> $cases
     */
    public function __construct(
        protected readonly string $class,
        protected readonly array $cases,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        if (!\is_string($value) || $value === '') {
            return false;
        }

        return \in_array($value, $this->cases, true);
    }

    /**
     * Converts a scalar representation of an enum to an enum case object.
     *
     * @throws InvalidValueException
     * @throws RuntimeExceptionInterface
     * @throws \Throwable
     */
    public function cast(mixed $value, Context $context): \UnitEnum
    {
        if (!$this->match($value, $context)) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        try {
            /** @var \UnitEnum */
            return \constant($this->class . '::' . $value);
        } catch (\Error $e) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
                previous: $e,
            );
        }
    }
}
