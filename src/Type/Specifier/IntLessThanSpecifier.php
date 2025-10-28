<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<int>
 */
final class IntLessThanSpecifier implements TypeSpecifierInterface
{
    public const DEFAULT_MAX_VALUE = IntRangeSpecifier::DEFAULT_MAX_VALUE;

    public function __construct(
        public readonly int $expected = self::DEFAULT_MAX_VALUE,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        \assert(\is_int($value));

        return $value < $this->expected;
    }
}
