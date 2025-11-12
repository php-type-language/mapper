<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsInRangeException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsInRangeException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TStmt of TypeStatement
 * @template-covariant TType of TypeInterface
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

        throw TemplateArgumentsNotSupportedException::becauseTooManyArguments(
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

        throw ShapeFieldsNotSupportedException::becauseTooManyShapeFields(
            type: $stmt,
        );
    }

    /**
     * @param int<0, max> $count
     *
     * @throws MissingTemplateArgumentsInRangeException
     * @throws TooManyTemplateArgumentsInRangeException
     */
    protected function expectTemplateArgumentsCount(NamedTypeNode $stmt, int $count): void
    {
        $this->expectTemplateArgumentsLessOrEqualThan($stmt, $count, $count);
        $this->expectTemplateArgumentsGreaterOrEqualThan($stmt, $count, $count);
    }

    /**
     * @param int<0, max> $max
     * @param int<0, max> $min
     *
     * @throws TooManyTemplateArgumentsInRangeException
     * @api
     *
     */
    protected function expectTemplateArgumentsLessThan(NamedTypeNode $stmt, int $max, int $min = 0): void
    {
        $this->expectTemplateArgumentsLessOrEqualThan($stmt, $max + 1, $min);
    }

    /**
     * @param int<0, max> $max
     * @param int<0, max> $min
     *
     * @throws TooManyTemplateArgumentsInRangeException
     * @api
     *
     */
    protected function expectTemplateArgumentsLessOrEqualThan(NamedTypeNode $stmt, int $max, int $min = 0): void
    {
        if ($stmt->arguments === null || $stmt->arguments->count() <= $max) {
            return;
        }

        throw TooManyTemplateArgumentsInRangeException::becauseHasRedundantArgument(
            minArgumentsCount: $min,
            maxArgumentsCount: $max,
            type: $stmt,
        );
    }

    /**
     * @param int<0, max> $min
     * @param int<0, max>|null $max
     *
     * @throws MissingTemplateArgumentsInRangeException
     * @api
     *
     */
    protected function expectTemplateArgumentsGreaterThan(NamedTypeNode $stmt, int $min, ?int $max = null): void
    {
        $this->expectTemplateArgumentsGreaterOrEqualThan($stmt, $min + 1, $max);
    }

    /**
     * @param int<0, max> $min
     * @param int<0, max>|null $max
     *
     * @throws MissingTemplateArgumentsInRangeException
     * @api
     *
     */
    protected function expectTemplateArgumentsGreaterOrEqualThan(NamedTypeNode $stmt, int $min, ?int $max = null): void
    {
        $actualArgumentsCount = $stmt->arguments?->count() ?? 0;

        if ($actualArgumentsCount >= $min) {
            return;
        }

        throw MissingTemplateArgumentsInRangeException::becauseNoRequiredArgument(
            minArgumentsCount: $min,
            maxArgumentsCount: $max ?? $min,
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

        throw TemplateArgumentHintsNotSupportedException::becauseTooManyHints(
            argument: $argument,
            type: $stmt,
        );
    }
}
