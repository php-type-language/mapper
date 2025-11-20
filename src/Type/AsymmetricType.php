<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;

/**
 * @template TOutResult of mixed = mixed
 * @template TInResult of mixed = mixed
 *
 * @template-implements TypeInterface<TOutResult|TInResult>
 */
abstract class AsymmetricType implements TypeInterface
{
    public function __construct(
        /**
         * @var TypeInterface<TOutResult>
         */
        private readonly TypeInterface $normalize,
        /**
         * @var TypeInterface<TInResult>
         */
        private readonly TypeInterface $denormalize,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        if ($context->direction->isOutput()) {
            return $this->normalize->match($value, $context);
        }

        return $this->denormalize->match($value, $context);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        if ($context->direction->isOutput()) {
            return $this->normalize->cast($value, $context);
        }

        return $this->denormalize->cast($value, $context);
    }
}
