<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<float>
 */
class FloatLiteralType implements TypeInterface
{
    private readonly float $value;

    public function __construct(
        int|float $value,
    ) {
        $this->value = (float) $value;
    }

    /**
     * @phpstan-assert-if-true int|float $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->value;
    }

    public function cast(mixed $value, Context $context): float
    {
        if ($value === $this->value) {
            return $value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
