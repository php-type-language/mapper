<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
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
     * @api
     *
     * @throws TemplateArgumentsNotSupportedException
     */
    protected function expectNoTemplateArguments(NamedTypeNode $stmt): void
    {
        if ($stmt->arguments === null) {
            return;
        }

        throw TemplateArgumentsNotSupportedException::becauseTemplateArgumentsNotSupported(
            passedArgumentsCount: $stmt->arguments->count(),
            type: $stmt,
        );
    }

    /**
     * @api
     *
     * @throws ShapeFieldsNotSupportedException
     */
    protected function expectNoShapeFields(NamedTypeNode $stmt): void
    {
        if ($stmt->fields === null) {
            return;
        }

        throw ShapeFieldsNotSupportedException::becauseShapeFieldsNotSupported(
            type: $stmt,
        );
    }

    /**
     * @api
     *
     * @param int<0, max> $max
     * @param int<0, max>|null $min
     *
     * @throws TooManyTemplateArgumentsException
     */
    protected function expectTemplateArgumentsLessThan(NamedTypeNode $stmt, int $max, ?int $min = null): void
    {
        $this->expectTemplateArgumentsLessOrEqualThan($stmt, $max + 1, $min);
    }

    /**
     * @api
     *
     * @param int<0, max> $max
     * @param int<0, max>|null $min
     *
     * @throws TooManyTemplateArgumentsException
     */
    protected function expectTemplateArgumentsLessOrEqualThan(NamedTypeNode $stmt, int $max, ?int $min = null): void
    {
        if ($stmt->arguments === null || $stmt->arguments->count() <= $max) {
            return;
        }

        throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
            passedArgumentsCount: $stmt->arguments->count(),
            minSupportedArgumentsCount: $min ?? $max,
            maxSupportedArgumentsCount: $max,
            type: $stmt,
        );
    }

    /**
     * @api
     *
     * @throws TemplateArgumentHintsNotSupportedException
     */
    protected function expectNoTemplateArgumentHint(TypeStatement $stmt, TemplateArgumentNode $argument): void
    {
        if ($argument->hint === null) {
            return;
        }

        throw TemplateArgumentHintsNotSupportedException::becauseTemplateArgumentHintsNotSupported(
            argument: $argument,
            type: $stmt,
        );
    }
}
