<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Type\Literal\NullLiteralNode;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\LogicalTypeNode\UnionTypeNode;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;
use TypeLang\Type\WrappingTypeNode\NullableTypeNode;

/**
 * Creates the types of `?T`, `T|null` and other unions. A union of one
 * type and `null` is the nullable type. The string types of a union are
 * tried last with coercion.
 *
 * @template-implements TypeBuilderInterface<mixed>
 */
final class UnionTypeBuilder implements TypeBuilderInterface
{
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if ($node instanceof NullableTypeNode) {
            return new NullableType($context->types->getType($node->type));
        }

        if (!$node instanceof UnionTypeNode) {
            return null;
        }

        $types = $strings = $others = [];
        $isNullable = false;

        foreach ($node->children as $child) {
            if ($child instanceof NullLiteralNode) {
                $isNullable = true;

                continue;
            }

            $type = $context->types->getType($child);
            $types[] = $type;

            if (self::isString($child)) {
                $strings[] = $type;

                continue;
            }

            $others[] = $type;
        }

        if ($isNullable && \count($types) === 1) {
            return new NullableType($types[0]);
        }

        if ($isNullable) {
            $types[] = $others[] = $context->types->getType(new NullLiteralNode());
        }

        $coercionOrder = [...$others, ...$strings];

        \assert($types !== [] && $coercionOrder !== []);

        return new UnionType($types, $coercionOrder);
    }

    /**
     * Returns {@see true} for a string type: `string`, a name ending with
     * `-string` (`non-empty-string`) or a string literal.
     */
    private static function isString(TypeNode $node): bool
    {
        if ($node instanceof StringLiteralNode) {
            return true;
        }

        if (!$node instanceof NamedTypeNode || $node->name->isFullyQualified()) {
            return false;
        }

        $name = $node->name->toLowerString();

        return $name === 'string' || \str_ends_with($name, '-string');
    }
}
