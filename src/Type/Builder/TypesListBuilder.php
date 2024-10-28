<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Runtime\Parser\TypeParserRuntimeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryRuntimeInterface;
use TypeLang\Mapper\Type\ArrayType;
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
        TypeRepositoryRuntimeInterface $types,
        TypeParserRuntimeInterface $parser,
    ): ArrayType {
        $type = $types->getTypeByStatement($statement->type);

        return new ArrayType($type);
    }
}
