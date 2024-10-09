<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Type\NullType;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;
use TypeLang\Parser\Node\Literal\NullLiteralNode;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-implements TypeBuilderInterface<NullLiteralNode|NamedTypeNode, NullType>
 */
final class NullTypeBuilder implements TypeBuilderInterface
{
    public function isSupported(TypeStatement $statement): bool
    {
        return $statement instanceof NullLiteralNode
            || ($statement instanceof NamedTypeNode
                && $statement->name->toLowerString() === 'null');
    }

    public function build(TypeStatement $statement, RepositoryInterface $types): NullType
    {
        if ($statement instanceof NamedTypeNode) {
            if ($statement->arguments !== null) {
                throw TemplateArgumentsNotSupportedException::becauseTemplateArgumentsNotSupported(
                    passedArgumentsCount: $statement->arguments->count(),
                    type: $statement,
                );
            }

            if ($statement->fields !== null) {
                throw ShapeFieldsNotSupportedException::becauseShapeFieldsNotSupported(
                    type: $statement,
                );
            }
        }

        return new NullType();
    }
}
