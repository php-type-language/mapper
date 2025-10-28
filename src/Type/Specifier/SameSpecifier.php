<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<mixed>
 */
final class SameSpecifier implements TypeSpecifierInterface
{
    public function __construct(
        private readonly mixed $expected,
    ) {}

    public function match(mixed $value, Context $context): bool
    {
        return $value === $this->expected;
    }
}
