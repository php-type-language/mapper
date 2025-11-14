<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends NamedTypeBuilder<TypeInterface>
 */
abstract class ObjectTypeBuilder extends NamedTypeBuilder
{
    public function build(TypeStatement $stmt, BuildingContext $context): TypeInterface
    {
        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($stmt instanceof NamedTypeNode);

        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        return $this->create();
    }

    abstract protected function create(): TypeInterface;
}
