<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Exception\Runtime\RuntimeException;
use TypeLang\Mapper\Type\Specifier\TypeSpecifierInterface;

/**
 * @template-covariant TResult of mixed = mixed
 */
interface TypeInterface extends TypeSpecifierInterface
{
    /**
     * @return TResult
     * @throws RuntimeException in case of known mapping issue
     * @throws \Throwable in case of internal error occurs
     */
    public function cast(mixed $value, Context $context): mixed;
}
