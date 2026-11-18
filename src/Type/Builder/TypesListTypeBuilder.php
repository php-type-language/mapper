<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\ArrayType;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;
use TypeLang\Type\WrappingTypeNode\TypesListTypeNode;

/**
 * Creates the type of `V[]`: an array of the elements of the type with the
 * keys of `array-key`, as `array<V>`.
 *
 * @template-implements CompositeTypeBuilderInterface<array<array-key, mixed>>
 */
final class TypesListTypeBuilder implements CompositeTypeBuilderInterface
{
    /**
     * @return ArrayType<array-key, mixed>|null
     * @throws TypeDefinitionException in case of the type of the elements
     *         cannot be built
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?ArrayType
    {
        if (!$node instanceof TypesListTypeNode) {
            return null;
        }

        $key = new NamedTypeNode(new Name('array-key'));

        /** @var ArrayType<array-key, mixed> */
        return new ArrayType($context->types->getType($key), $context->types->getType($node->type));
    }

    /**
     * Returns the type of the elements or `array-key` at any key.
     */
    public function tryGetNestedType(TypeNode $node, int|string $key, bool $isKey): ?TypeNode
    {
        if (!$node instanceof TypesListTypeNode) {
            return null;
        }

        if ($isKey) {
            return new NamedTypeNode(new Name('array-key'));
        }

        return $node->type;
    }
}
