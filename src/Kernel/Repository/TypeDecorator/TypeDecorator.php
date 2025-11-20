<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\TypeDecorator;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Type\MatchedResult;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = mixed
 *
 * @template-implements TypeDecoratorInterface<TResult, TMatch>
 */
abstract class TypeDecorator implements TypeDecoratorInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TResult, TMatch>
         */
        protected readonly TypeInterface $delegate,
    ) {}

    public function getDecoratedType(): TypeInterface
    {
        if ($this->delegate instanceof TypeDecoratorInterface) {
            /** @var TypeInterface<TResult, TMatch> */
            return $this->delegate->getDecoratedType();
        }

        return $this->delegate;
    }

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        return $this->delegate->match($value, $context);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        return $this->delegate->cast($value, $context);
    }
}
