<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context;

/**
 * @template-implements TypeInterface<float>
 */
class FloatLiteralType implements TypeInterface
{
    private readonly float $expected;

    public function __construct(float|int $value)
    {
        $this->expected = (float) $value;
    }

    public function match(mixed $value, Context $context): bool
    {
        if (\is_int($value)) {
            return (float) $value === $this->expected;
        }

        return $value === $this->expected;
    }

    public function cast(mixed $value, Context $context): float
    {
        if ($this->match($value, $context)) {
            /** @var float|int $value */
            return (float) $value;
        }

        throw InvalidValueException::createFromContext(
            value: $value,
            context: $context,
        );
    }
}
