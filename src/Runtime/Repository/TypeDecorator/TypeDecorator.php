<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\TypeDecorator;

use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Context;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Runtime\Repository
 */
abstract class TypeDecorator implements TypeDecoratorInterface
{
    public function __construct(
        protected readonly TypeInterface $delegate,
    ) {}

    public function getDecoratedType(): TypeInterface
    {
        if ($this->delegate instanceof TypeDecoratorInterface) {
            return $this->delegate->getDecoratedType();
        }

        return $this->delegate;
    }

    public function match(mixed $value, Context $context): bool
    {
        return $this->delegate->match($value, $context);
    }

    public function cast(mixed $value, Context $context): mixed
    {
        return $this->delegate->cast($value, $context);
    }

    public function __serialize(): array
    {
        throw new \LogicException(<<<'MESSAGE'
            Cannot serialize a type decorator.

            Please disable cache in case you are using debug mode.
            MESSAGE);
    }
}
