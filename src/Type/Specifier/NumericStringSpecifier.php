<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<string>
 */
final class NumericStringSpecifier implements TypeSpecifierInterface
{
    public function match(mixed $value, Context $context): bool
    {
        \assert(\is_string($value));

        return \is_numeric($value);
    }
}
