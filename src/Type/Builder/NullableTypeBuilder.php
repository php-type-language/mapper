<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Repository\Repository;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Parser\Node\Stmt\NullableTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see NullableType} from "?Type" syntax.
 *
 * @template-implements TypeBuilderInterface<NullableTypeNode<TypeStatement>, NullableType>
 */
class NullableTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NullableTypeNode;
    }

    public function build(TypeStatement $statement, Repository $types): NullableType
    {
        $type = $types->getByStatement($statement->type);

        return new NullableType($type);
    }
}
