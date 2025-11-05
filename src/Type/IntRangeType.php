<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-implements TypeInterface<int>
 */
class IntRangeType implements TypeInterface
{
    public const DEFAULT_INT_MIN = \PHP_INT_MIN;
    public const DEFAULT_INT_MAX = \PHP_INT_MAX;

    public function __construct(
        protected readonly int $min = self::DEFAULT_INT_MIN,
        protected readonly int $max = self::DEFAULT_INT_MAX,
    ) {}

    /**
     * @phpstan-assert-if-true int $value
     */
    public function match(mixed $value, Context $context): bool
    {
        return \is_int($value)
            && $value >= $this->min
            && $value <= $this->max;
    }

    public function cast(mixed $value, Context $context): int
    {
        if ($this->match($value, $context)) {
            return $value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
