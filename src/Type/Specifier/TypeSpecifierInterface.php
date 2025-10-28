<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Specifier;

use TypeLang\Mapper\Context\Context;

/**
 * @template T of mixed = mixed
 */
interface TypeSpecifierInterface
{
    /**
     * Checks that the value matches the selected type
     *
     * @param T $value
     */
    public function match(mixed $value, Context $context): bool;
}
