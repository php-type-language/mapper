<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\InvalidValueException;

use TypeLang\Mapper\Diagnostics\Path\Segment\KeySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\PropertySegment;
use TypeLang\Mapper\Diagnostics\Path\Segment\SegmentInterface;
use TypeLang\Mapper\Type\Builder\CompositeTypeBuilderInterface;
use TypeLang\Mapper\Type\Repository\InspectableTypeRepositoryInterface;
use TypeLang\Type\Literal\NullLiteralNode;
use TypeLang\Type\LogicalTypeNode\UnionTypeNode;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;
use TypeLang\Type\WrappingTypeNode\NullableTypeNode;

/**
 * Finds the declared type of a nested value by the path from the value of
 * the declaration. The type at each key is told by the builder the type is
 * built by, in case of it is a builder of a composite type: the element of
 * `list<T>`, the field of `array{...}`. `?T` and `T|null` are passed
 * through to `T`.
 *
 * ```
 * // list<array{id: int}>, [3, id]
 * $finder->find($type, $path->segments); // int
 * ```
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class DeclaredTypeFinder
{
    public function __construct(
        /**
         * Types and their builders, {@see null} finds the declared type of
         * the value itself only
         */
        private readonly ?InspectableTypeRepositoryInterface $types = null,
    ) {}

    /**
     * Returns the type at the path, the type of the key the last segment
     * points to in case of `$isKey`. {@see null} in case of the path passes
     * a type whose parts are unknown, such as a class, a union or a type no
     * builder tells the parts of.
     *
     * @param list<SegmentInterface> $segments
     */
    public function find(TypeNode $type, array $segments, bool $isKey = false): ?TypeNode
    {
        $last = \array_key_last($segments);

        foreach ($segments as $index => $segment) {
            $key = match (true) {
                $segment instanceof KeySegment => $segment->key,
                $segment instanceof PropertySegment => $segment->property,
                default => null,
            };

            if ($key === null) {
                return null;
            }

            $type = $this->findNested($type, $key, $isKey && $index === $last);

            if ($type === null) {
                return null;
            }
        }

        return $type;
    }

    /**
     * Returns the type at the key told by the builder of the type,
     * {@see null} in case of the builder is not a builder of a composite type
     * or the type cannot be built.
     */
    private function findNested(TypeNode $type, int|string $key, bool $isKey): ?TypeNode
    {
        $type = self::unwrapNull($type);

        if ($type === null || $this->types === null) {
            return null;
        }

        try {
            $this->types->getType($type);
        } catch (\Throwable) {
            return null;
        }

        $builder = $this->types->findBuilder($type);

        if (!$builder instanceof CompositeTypeBuilderInterface) {
            return null;
        }

        return $builder->tryGetNestedType($type, $key, $isKey);
    }

    /**
     * Returns `T` of `?T` and `T|null`, the type itself in case of it is not
     * nullable, {@see null} for a union with other members.
     */
    private static function unwrapNull(TypeNode $type): ?TypeNode
    {
        if ($type instanceof NullableTypeNode) {
            return self::unwrapNull($type->type);
        }

        if (!$type instanceof UnionTypeNode) {
            return $type;
        }

        $members = [];

        foreach ($type as $member) {
            if ($member instanceof NullLiteralNode) {
                continue;
            }

            if ($member instanceof NamedTypeNode && $member->name->is('null')) {
                continue;
            }

            $members[] = $member;
        }

        if (\count($members) !== 1) {
            return null;
        }

        return self::unwrapNull($members[0]);
    }
}
