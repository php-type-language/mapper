<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\TypeDecorator;

use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = mixed
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-extends TypeInterface<TResult, TMatch>
 */
interface TypeDecoratorInterface extends TypeInterface
{
    /**
     * @return TypeInterface<TResult, TMatch>
     */
    public function getDecoratedType(): TypeInterface;
}
