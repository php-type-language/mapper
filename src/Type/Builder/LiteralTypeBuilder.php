<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Type\LiteralType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Literal\BoolLiteralNode;
use TypeLang\Type\Literal\FloatLiteralNode;
use TypeLang\Type\Literal\IntLiteralNode;
use TypeLang\Type\Literal\ScalarLiteralNode;
use TypeLang\Type\Literal\StringLiteralNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Creates the type of a literal, such as `42`, `'active'` or `true`. The
 * value is coerced by the type of its kind from the repository.
 *
 * @template-implements TypeBuilderInterface<int|float|string|bool>
 */
final class LiteralTypeBuilder implements TypeBuilderInterface
{
    /**
     * @return LiteralType<int|float|string|bool>|null
     * @throws TypeDefinitionException in case of a nested type cannot be built
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?LiteralType
    {
        if (!$node instanceof ScalarLiteralNode) {
            return null;
        }

        $kind = match (true) {
            $node instanceof IntLiteralNode => 'int',
            $node instanceof FloatLiteralNode => 'float',
            $node instanceof StringLiteralNode => 'string',
            $node instanceof BoolLiteralNode => 'bool',
            default => null,
        };

        $value = $node->value;

        if ($kind === null || !\is_scalar($value)) {
            return null;
        }

        /** @var TypeInterface<int|float|string|bool> */
        $type = $context->types->getType(new NamedTypeNode(new Name($kind)));

        return new LiteralType($value, $type);
    }
}
