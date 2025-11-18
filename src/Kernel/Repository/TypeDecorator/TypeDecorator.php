<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\TypeDecorator;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-covariant TResult of mixed = mixed
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-implements TypeDecoratorInterface<TResult>
 */
abstract class TypeDecorator implements TypeDecoratorInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TResult>
         */
        protected readonly TypeInterface $delegate,
    ) {}

    public function getDecoratedType(): TypeInterface
    {
        if ($this->delegate instanceof TypeDecoratorInterface) {
            /** @var TypeInterface<TResult> */
            return $this->delegate->getDecoratedType();
        }

        return $this->delegate;
    }

    public function match(mixed $value, RuntimeContext $context): bool
    {
        return $this->delegate->match($value, $context);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        return $this->delegate->cast($value, $context);
    }
}
