<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\BackedEnumType\BackedEnumTypeNormalizer;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template-extends Builder<NamedTypeNode, BackedEnumTypeNormalizer>
 */
class BackedEnumToScalarTypeBuilder extends BackedEnumTypeBuilder
{
    protected function create(string $class, TypeInterface $type): BackedEnumTypeNormalizer
    {
        return new BackedEnumTypeNormalizer($class);
    }
}
