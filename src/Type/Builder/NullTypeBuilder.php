<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Context\BuildingContext;
use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Type\NullType;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NullLiteralNode|NamedTypeNode, NullType>
 */
class NullTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $stmt): bool
    {
        if ($stmt instanceof NullLiteralNode) {
            return true;
        }

        return $stmt instanceof NamedTypeNode
            && $stmt->name->toLowerString() === 'null';
    }

    /**
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(TypeStatement $stmt, BuildingContext $context): NullType
    {
        if ($stmt instanceof NullLiteralNode) {
            return new NullType();
        }

        /** @phpstan-ignore-next-line : Additional DbC assertion */
        assert($stmt instanceof NamedTypeNode);

        $this->expectNoShapeFields($stmt);
        $this->expectNoTemplateArguments($stmt);

        return new NullType();
    }
}
