<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BackedEnumType\BackedEnumTypeDenormalizer;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template-extends Builder<NamedTypeNode, BackedEnumTypeDenormalizer>
 */
class BackedEnumFromScalarTypeBuilder extends BackedEnumTypeBuilder
{
    protected function create(string $class, TypeInterface $type): BackedEnumTypeDenormalizer
    {
        return new BackedEnumTypeDenormalizer($class, $type);
    }
}
