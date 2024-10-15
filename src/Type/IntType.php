<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Runtime\Context\LocalContext;

class IntType implements TypeInterface
{
    public const DEFAULT_INT_MIN = \PHP_INT_MIN;
    public const DEFAULT_INT_MAX = \PHP_INT_MAX;

    public function __construct(
        protected readonly int $min = self::DEFAULT_INT_MIN,
        protected readonly int $max = self::DEFAULT_INT_MAX,
        protected readonly bool $userDefinedRange = true,
    ) {}

    public function match(mixed $value, LocalContext $context): bool
    {
        return \is_int($value)
            && $value >= $this->min
            && $value <= $this->max;
    }

    /**
     * Converts incoming value to the int (in case of strict types is disabled).
     *
     * @throws InvalidValueException
     */
    public function cast(mixed $value, LocalContext $context): int
    {
        if (!\is_int($value) || $value > $this->max || $value < $this->min) {
            throw InvalidValueException::createFromContext(
                value: $value,
                context: $context,
            );
        }

        return $value;
    }
}
