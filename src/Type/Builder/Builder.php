<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TStmt of TypeStatement
 *
 * @template-covariant TType of TypeInterface
 *
 * @template-implements TypeBuilderInterface<TStmt, TType>
 */
abstract class Builder implements TypeBuilderInterface
{
    /**
     * @param TStmt $stmt
     *
     * @throws TemplateArgumentsNotSupportedException
     */
    protected static function assertNoTemplateArguments(TypeStatement $stmt): void
    {
        $stmt instanceof NamedTypeNode
            && $stmt->arguments !== null
            && throw TemplateArgumentsNotSupportedException::becauseTemplateArgumentsNotSupported($stmt->arguments->count(), $stmt);
    }

    /**
     * @param TStmt $stmt
     *
     * @throws ShapeFieldsNotSupportedException
     */
    protected static function assertNoShapeFields(TypeStatement $stmt): void
    {
        $stmt instanceof NamedTypeNode
            && $stmt->fields !== null
            && throw ShapeFieldsNotSupportedException::becauseShapeFieldsNotSupported($stmt);
    }
}
