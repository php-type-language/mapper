<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Platform\Standard\Type\NullableType;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Platform\Standard\Type\UnionType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
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

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): TypeInterface {
        $result = [];
        $nullable = false;

        foreach ($statement->statements as $leaf) {
            if ($leaf instanceof NullLiteralNode) {
                $nullable = true;
            } else {
                $result[] = $types->getTypeByStatement($leaf);
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
