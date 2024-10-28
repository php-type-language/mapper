<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\TypeDecorator;

use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
interface TypeDecoratorInterface extends TypeInterface
{
    public function getDecoratedType(): TypeInterface;
}
