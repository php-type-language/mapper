<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Platform\Standard\Type\NullableType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
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

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): NullableType {
        $type = $types->getTypeByStatement($statement->type);

        return new NullableType($type);
    }
}
