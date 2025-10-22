<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Mapper\Type\UnitEnumType\UnitEnumTypeNormalizer;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * @template-extends Builder<NamedTypeNode, TypeInterface>
 */
class UnitEnumToScalarTypeBuilder extends UnitEnumTypeBuilder
{
    protected function create(string $class, array $cases, TypeInterface $type): TypeInterface
    {
        return new UnitEnumTypeNormalizer($class);
    }
}
