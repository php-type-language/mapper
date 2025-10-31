<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository\TypeDecorator;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Type\Coercer\TypeCoercerInterface;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * @template-covariant TResult of mixed = mixed
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-extends TypeDecorator<TResult>
 */
final class CoercibleType extends TypeDecorator
{
    public function __construct(
        /**
         * @var TypeCoercerInterface<TResult>
         */
        private readonly TypeCoercerInterface $coercer,
        TypeInterface $delegate,
    ) {
        parent::__construct($delegate);
    }

    public function match(mixed $value, Context $context): bool
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->coercer->coerce($value, $context);
        }

        return parent::match($value, $context);
    }

    public function cast(mixed $value, Context $context): mixed
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->coercer->coerce($value, $context);
        }

        return parent::cast($value, $context);
    }
}
