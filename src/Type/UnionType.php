<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\Path\Entry\UnionLeafEntry;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Type\UnionType\UnionSelection;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-covariant TMatch of mixed= mixed
 *
 * @template-implements TypeInterface<TResult, TMatch>
 */
class UnionType implements TypeInterface
{
    public function __construct(
        /**
         * @var non-empty-list<TypeInterface<TResult, TMatch>>
         */
        private readonly array $types,
    ) {}

    /**
     * Finds a child supported type from their {@see $types} list by value.
     *
     * @return UnionSelection<TResult, TMatch>|null
     */
    protected function select(mixed $value, RuntimeContext $context, bool $strict = true): ?UnionSelection
    {
        foreach ($this->types as $index => $type) {
            $entrance = $context->enter(
                value: $value,
                entry: new UnionLeafEntry($index),
                config: $context->withStrictTypes($strict),
            );

            $result = $type->match($value, $entrance);

            if ($result !== null) {
                return new UnionSelection($type, $result);
            }
        }

        return null;
    }

    /**
     * Performs a cascading search of types based on value.
     *
     * 1. Perform a primary search using strict checks
     * 2. Perform a secondary search using type coercions:
     *    - in case of the primary search did not work
     *    - and if type coercions are enabled
     *
     * @return UnionSelection<TResult, TMatch>|null
     */
    protected function selectWithFallback(mixed $value, RuntimeContext $context): ?UnionSelection
    {
        if ($context->isStrictTypesEnabled()) {
            return $this->select($value, $context);
        }

        return $this->select($value, $context)
            ?? $this->select($value, $context, false);
    }

    public function match(mixed $value, RuntimeContext $context): ?MatchedResult
    {
        return $this->selectWithFallback($value, $context)
            ?->result;
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $selection = $this->selectWithFallback($value, $context);

        if ($selection !== null) {
            return $selection->type->cast($value, $context);
        }

        throw InvalidValueException::createFromContext($context);
    }
}
