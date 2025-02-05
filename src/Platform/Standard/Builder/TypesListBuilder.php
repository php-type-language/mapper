<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Platform\Standard\Type\ArrayType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
use TypeLang\Parser\Node\Stmt\TypesListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see ArrayType} from the "Type[]" syntax.
 *
 * @template-implements TypeBuilderInterface<TypesListNode<TypeStatement>, ArrayType>
 */
class TypesListBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof TypesListNode;
    }

    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ArrayType {
        $type = $types->getTypeByStatement($statement->type);

        return new ArrayType($type);
    }
}
