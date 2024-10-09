<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\MixedType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NamedTypeNode, MixedType>
 */
final class MixedTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === 'mixed';
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): MixedType
    {
        self::assertNoTemplateArguments($statement);
        self::assertNoShapeFields($statement);

        return new MixedType($statement->name->toString());
    }
}
