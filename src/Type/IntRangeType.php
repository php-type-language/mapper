<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
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
        /**
         * @var TypeInterface<int>
         */
        protected readonly TypeInterface $type = new IntType(),
    ) {}

    /**
     * @phpstan-assert-if-true int $value
     */
    public function match(mixed $value, RuntimeContext $context): bool
    {
        return $this->type->match($value, $context)
            && $value >= $this->min
            && $value <= $this->max;
    }

    public function cast(mixed $value, RuntimeContext $context): int
    {
        if ($this->match($value, $context)) {
            return $value;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
