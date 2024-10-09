<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\NullableType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see NullableType} from "?Type" syntax.
 *
 * @template-implements TypeBuilderInterface<NullableTypeNode<TypeStatement>, NullableType>
 */
final class NullableTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NullableTypeNode;
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): NullableType
    {
        return new NullableType($types->getByStatement($statement->type));
    }
}
