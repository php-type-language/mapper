<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsInRangeException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
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
    protected function expectNoTemplateArguments(TypeStatement $stmt): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectNoTemplateArguments($stmt);
    }

    /**
     * @api
     *
     * @throws ShapeFieldsNotSupportedException
     */
    protected function expectNoShapeFields(TypeStatement $stmt): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        if ($stmt->fields === null) {
            return;
        }

        throw ShapeFieldsNotSupportedException::becauseTooManyShapeFields($stmt);
    }

    /**
     * @api
     *
     * @throws TooManyTemplateArgumentsException
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     */
    protected function expectTemplateArgumentsCount(TypeStatement $stmt, int $count): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectTemplateArgumentsCount($stmt, $count);
    }

    /**
     * @api
     *
     * @throws TooManyTemplateArgumentsException
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     * @throws TooManyTemplateArgumentsInRangeException
     * @throws MissingTemplateArgumentsInRangeException
     */
    protected function expectTemplateArgumentsInRange(TypeStatement $stmt, int $from, int $to): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectTemplateArgumentsCountInRange($stmt, $from, $to);
    }

    /**
     * @api
     *
     * @throws MissingTemplateArgumentsException
     */
    protected function expectTemplateArgumentsLessThan(TypeStatement $stmt, int $count): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectTemplateArgumentsCountLessThan($stmt, $count);
    }

    /**
     * @api
     *
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     */
    protected function expectTemplateArgumentsLessOrEqualThan(TypeStatement $stmt, int $count): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectTemplateArgumentsCountLessOrEqualThan($stmt, $count);
    }

    /**
     * @api
     *
     * @throws TooManyTemplateArgumentsException
     */
    protected function expectTemplateArgumentsGreaterThan(TypeStatement $stmt, int $count): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectTemplateArgumentsCountGreaterThan($stmt, $count);
    }

    /**
     * @api
     *
     * @throws TooManyTemplateArgumentsException
     */
    protected function expectTemplateArgumentsGreaterOrEqualThan(TypeStatement $stmt, int $count): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectTemplateArgumentsCountGreaterOrEqualThan($stmt, $count);
    }

    /**
     * @api
     *
     * @throws TemplateArgumentHintsNotSupportedException
     */
    protected function expectNoTemplateArgumentHint(TypeStatement $stmt, TemplateArgumentNode $argument): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectNoTemplateArgumentHints($stmt, $argument);
    }

    /**
     * @api
     *
     * @throws TemplateArgumentHintsNotSupportedException
     */
    protected function expectNoAnyTemplateArgumentHint(TypeStatement $stmt): void
    {
        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        Assert::expectNoAnyTemplateArgumentsHints($stmt);
    }
}
