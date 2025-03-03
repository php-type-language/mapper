<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Standard\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Platform\Standard\Type\NullType;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;
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
     * @throws ShapeFieldsNotSupportedException
     * @throws TemplateArgumentsNotSupportedException
     */
    public function build(
        TypeStatement $statement,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): NullType {
        if ($statement instanceof NullLiteralNode) {
            return new NullType();
        }

        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new NullType();
    }
}
