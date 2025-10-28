<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<string>
 */
final class UppercaseStringSpecifier implements TypeSpecifierInterface
{
    public function match(mixed $value, Context $context): bool
    {
        \assert(\is_string($value));

        if ($value === '') {
            return true;
        }

        if (\function_exists('\\ctype_lower')) {
            return !\ctype_lower($value);
        }

        return \preg_match('/[a-z]/', $value) <= 0;
    }
}
