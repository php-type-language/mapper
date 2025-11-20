<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed = mixed
 *
 * @template-implements TypeInterface<TResult, TMatch>
 */
abstract class LiteralType implements TypeInterface
{
    public function __construct(
        /**
         * @var TResult
         */
        protected readonly mixed $value,
        /**
         * @var TypeInterface<TResult, TMatch>
         */
        protected readonly TypeInterface $type,
    ) {}

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        $result = $this->type->match($value, $context);

        return $result?->if($result->value === $this->value);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $coerced = $this->type->cast($value, $context);

        if ($coerced === $this->value) {
            return $coerced;
        }

        throw InvalidValueException::createFromContext($context);
    }
}
