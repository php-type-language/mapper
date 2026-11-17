<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\ObjectType;
use TypeLang\Mapper\Type\ShapeType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the types of `object` and `\stdClass`. A {@see \stdClass} is an
 * object shape with no declared fields, its properties are `mixed`.
 *
 * @template-implements TypeBuilderInterface<object|array<array-key, mixed>>
 */
final class ObjectTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return TypeInterface<object|array<array-key, mixed>>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if (
            !$node instanceof NamedTypeNode
            || $node->arguments !== null
            || $node->fields !== null
        ) {
            return null;
        }

        $name = \strtolower(\ltrim($node->name->toString(), '\\'));

        if ($name !== 'object' && $name !== 'stdclass') {
            return null;
        }

        $mixed = $context->types->getType(new NamedTypeNode(new Name('mixed')));

        if ($name === 'stdclass') {
            return new ShapeType([], \stdClass::class, rest: $mixed, isObject: true);
        }

        return new ObjectType(new ShapeType([], 'object', rest: $mixed, isObject: true), $mixed);
    }
}
