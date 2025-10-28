<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<int>
 */
final class IntGreaterThanOrEqualSpecifier implements TypeSpecifierInterface
{
    public const DEFAULT_MIN_VALUE = IntRangeSpecifier::DEFAULT_MIN_VALUE;

    public function __construct(
        public readonly int $expected = self::DEFAULT_MIN_VALUE,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        \assert(\is_int($value));

        return $value >= $this->expected;
    }
}
