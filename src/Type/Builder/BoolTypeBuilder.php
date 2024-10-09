<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BoolType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, BoolType>
 */
final class BoolTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === 'bool';
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): BoolType
    {
        self::assertNoTemplateArguments($statement);
        self::assertNoShapeFields($statement);

        return new BoolType($statement->name->toString());
    }
}
