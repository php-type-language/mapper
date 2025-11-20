<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\TypeDecorator;

use TypeLang\Mapper\Coercer\TypeCoercerInterface;
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
 * @template-extends TypeDecorator<TResult, TMatch>
 */
final class CoercibleType extends TypeDecorator
{
    /**
     * @param TypeInterface<TResult, TMatch> $delegate
     */
    public function __construct(
        /**
         * @var TypeCoercerInterface<TMatch>
         */
        private readonly TypeCoercerInterface $coercer,
        TypeInterface $delegate,
    ) {
        parent::__construct($delegate);
    }

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->coercer->tryCoerce($value, $context);
        }

        return parent::match($value, $context);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        if (!$context->isStrictTypesEnabled()) {
            $value = $this->coercer->tryCoerce($value, $context);
        }

        return parent::cast($value, $context);
    }
}
