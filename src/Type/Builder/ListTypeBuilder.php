<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Registry\RegistryInterface;
use TypeLang\Mapper\Type\ListType;
use TypeLang\Mapper\Type\TypeInterface;
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

    public function build(TypeStatement $type, RegistryInterface $context): TypeInterface
    {
        assert($type instanceof TypesListNode);

        return new ListType($context->get($type->type));
    }
}
