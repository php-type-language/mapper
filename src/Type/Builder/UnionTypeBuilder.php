<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Mapper\Type\UnionType;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Node\Stmt\UnionTypeNode;

final class UnionTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof UnionTypeNode;
    }

    public function build(TypeStatement $type, RepositoryInterface $context): UnionType
    {
        assert($type instanceof UnionTypeNode);

        $types = [];

        foreach ($type->statements as $statement) {
            $types[] = $context->get($statement);
        }

        return new UnionType($types);
    }
}
