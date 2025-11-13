<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-implements TypeBuilderInterface<UnionTypeNode<TypeStatement>, TypeInterface>
 */
class UnionTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof UnionTypeNode;
    }

    public function build(TypeStatement $statement, BuildingContext $context): TypeInterface
    {
        $result = [];
        $nullable = false;

        foreach ($statement->statements as $leaf) {
            if ($leaf instanceof NullLiteralNode) {
                $nullable = true;
            } else {
                $result[] = $context->getTypeByStatement($leaf);
            }
        }

        $result = match (\count($result)) {
            0 => throw new \InvalidArgumentException('Invalid union leaves'),
            1 => \reset($result),
            default => new UnionType($result),
        };

        if ($nullable === true) {
            return new NullableType($result);
        }

        return $result;
    }
}
