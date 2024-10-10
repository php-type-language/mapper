<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Type\NullType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
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

    public function build(TypeStatement $statement, RepositoryInterface $types): NullType
    {
        /** @var NamedTypeNode|NullLiteralNode $statement : PhpStorm autocomplete */
        if ($statement instanceof NullLiteralNode) {
            return new NullType();
        }

        $this->expectNoShapeFields($statement);
        $this->expectNoTemplateArguments($statement);

        return new NullType();
    }
}
