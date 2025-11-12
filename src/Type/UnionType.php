<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Context\MappingContext;
use TypeLang\Mapper\Context\Path\Entry\UnionLeafEntry;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

/**
 * @template-covariant TResult of mixed = mixed
 * @template-implements TypeInterface<TResult>
 */
class UnionType implements TypeInterface
{
    public function __construct(
        /**
         * @var non-empty-list<TypeInterface<TResult>>
         */
        private readonly array $types,
    ) {}

    /**
     * Finds a child supported type from their {@see $types} list by value.
     *
     * @return TypeInterface<TResult>|null
     */
    protected function findType(mixed $value, MappingContext $context, bool $strict = true): ?TypeInterface
    {
        foreach ($this->types as $index => $type) {
            $entrance = $context->enter(
                value: $value,
                entry: new UnionLeafEntry($index),
                config: $context->withStrictTypes($strict),
            );

            if ($type->match($value, $entrance)) {
                return $type;
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
     * @return TypeInterface<TResult>|null
     */
    protected function findTypeWithFallback(mixed $value, MappingContext $context): ?TypeInterface
    {
        if ($context->isStrictTypesEnabled()) {
            return $this->findType($value, $context);
        }

        return $this->findType($value, $context)
            ?? $this->findType($value, $context, false);
    }

    public function match(mixed $value, MappingContext $context): bool
    {
        return $this->findTypeWithFallback($value, $context) !== null;
    }

    public function cast(mixed $value, MappingContext $context): mixed
    {
        $type = $this->findTypeWithFallback($value, $context);

        if ($type !== null) {
            return $type->cast($value, $context);
        }

        throw InvalidValueException::createFromContext($context);
    }
}
