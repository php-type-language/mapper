<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Runtime\Repository\TypeRepository;
use TypeLang\Mapper\Type\NullType;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends Builder<NullLiteralNode|NamedTypeNode, NullType>
 */
class NullTypeBuilder extends Builder
{
    public function isSupported(TypeStatement $statement): bool
    {
        if ($statement instanceof NullLiteralNode) {
            return true;
        }

        return $statement instanceof NamedTypeNode
            && $statement->name->toLowerString() === 'null';
    }

    /**
     * @inheritDoc
     *
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(TypeStatement $statement, TypeRepository $types): NullType
    {
        if ($statement instanceof NullLiteralNode) {
            return new NullType();
        }

        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new NullType();
    }
}
