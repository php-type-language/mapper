<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Type\TypeNode;

/**
 * Builder of a composite type, also telling the declared types of the
 * values and the keys inside the value of the type: the declared type of a
 * violation of a nested value is found by them. Only the builder knows what
 * the parts of its node mean: the fields of `array{...}` are values, the
 * fields of `Set{weak: true}` may be options.
 *
 * ```
 * // Collection<User>
 * $builder->tryGetNestedType($node, 3, isKey: false); // User
 * ```
 *
 * The declared types of the properties of a class are given by the type of
 * the class itself.
 *
 * @template-covariant T
 *
 * @template-extends TypeBuilderInterface<T>
 */
interface CompositeTypeBuilderInterface extends TypeBuilderInterface
{
    /**
     * Returns the declared type of the value at the key, of the key itself
     * in case of `$isKey`. {@see null} in case of the node is not supported
     * by the builder or has no such value.
     */
    public function tryGetNestedType(TypeNode $node, int|string $key, bool $isKey): ?TypeNode;
}
