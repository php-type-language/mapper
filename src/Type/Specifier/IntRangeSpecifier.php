<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<int>
 */
final class IntRangeSpecifier implements TypeSpecifierInterface
{
    public const DEFAULT_MAX_VALUE = \PHP_INT_MAX;
    public const DEFAULT_MIN_VALUE = \PHP_INT_MIN;

    public function __construct(
        public readonly int $min = self::DEFAULT_MIN_VALUE,
        public readonly int $max = self::DEFAULT_MAX_VALUE,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        \assert(\is_int($value));

        return $value >= $this->min
            && $value <= $this->max;
    }
}
