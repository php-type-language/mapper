<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template-implements TypeSpecifierInterface<string>
 */
final class LowercaseStringSpecifier implements TypeSpecifierInterface
{
    public function match(mixed $value, Context $context): bool
    {
        \assert(\is_string($value));

        if ($value === '') {
            return true;
        }

        if (\function_exists('\\ctype_upper')) {
            return !\ctype_upper($value);
        }

        return \preg_match('/[A-Z]/', $value) <= 0;
    }
}
