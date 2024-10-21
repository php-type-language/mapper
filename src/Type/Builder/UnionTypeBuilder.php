<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Repository\Repository;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

/**
 * @template-implements TypeBuilderInterface<UnionTypeNode<TypeStatement>, UnionType>
 */
class UnionTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof UnionTypeNode;
    }

    public function build(TypeStatement $statement, Repository $types): UnionType
    {
        $result = [];

        foreach ($statement->statements as $leaf) {
            $result[] = $types->getByStatement($leaf);
        }

        return new UnionType($result);
    }
}
