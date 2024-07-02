<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\ListType;
use TypeLang\Parser\Node\Stmt\TypesListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Creates a {@see ListType} from the "Type[]" syntax.
 */
final class ListTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof TypesListNode;
    }

    /**
     * @throws TypeNotFoundException
     */
    public function build(TypeStatement $type, RegistryInterface $context): ListType
    {
        assert($type instanceof TypesListNode);

        return new ListType(
            name: 'list',
            type: $context->get($type->type),
        );
    }
}
