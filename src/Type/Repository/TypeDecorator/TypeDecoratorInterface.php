<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\TypeDecorator;

use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-covariant TResult of mixed = mixed
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-extends TypeInterface<TResult>
 */
interface TypeDecoratorInterface extends TypeInterface
{
    /**
     * @return TypeInterface<TResult>
     */
    public function getDecoratedType(): TypeInterface;
}
