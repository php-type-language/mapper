<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

class FloatLiteralType extends FloatType
{
    private readonly float $value;

    public function __construct(int|float $value)
    {
        $this->value = (float) $value;
    }

    /**
     * @phpstan-assert-if-true int|float $value
     */
    public function match(mixed $value, Context $context): bool
    {
        if (\is_int($value)) {
            return (float) $value === $this->value;
        }

        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): float
    {
        if ($this->match($value, $context)) {
            return (float) $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
