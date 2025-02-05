<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Platform\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Platform\Type\TypeInterface;
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
    protected static function expectNoTemplateArguments(NamedTypeNode $stmt): void
    {
        if ($stmt->arguments === null) {
            return;
        }

        throw TemplateArgumentsNotSupportedException::becauseTemplateArgumentsNotSupported(
            type: $stmt,
        );
    }

    /**
     * @api
     *
     * @throws ShapeFieldsNotSupportedException
     */
    protected static function expectNoShapeFields(NamedTypeNode $stmt): void
    {
        if ($stmt->fields === null) {
            return;
        }

        throw ShapeFieldsNotSupportedException::becauseShapeFieldsNotSupported(
            type: $stmt,
        );
    }

    /**
     * @param int<0, max> $count
     *
     * @throws MissingTemplateArgumentsException
     * @throws TooManyTemplateArgumentsException
     */
    protected static function expectTemplateArgumentsCount(NamedTypeNode $stmt, int $count): void
    {
        static::expectTemplateArgumentsLessOrEqualThan($stmt, $count, $count);
        static::expectTemplateArgumentsGreaterOrEqualThan($stmt, $count, $count);
    }

    /**
     * @api
     *
     * @param int<0, max> $max
     * @param int<0, max> $min
     *
     * @throws TooManyTemplateArgumentsException
     */
    protected static function expectTemplateArgumentsLessThan(NamedTypeNode $stmt, int $max, int $min = 0): void
    {
        static::expectTemplateArgumentsLessOrEqualThan($stmt, $max + 1, $min);
    }

    /**
     * @api
     *
     * @param int<0, max> $max
     * @param int<0, max> $min
     *
     * @throws TooManyTemplateArgumentsException
     */
    protected static function expectTemplateArgumentsLessOrEqualThan(NamedTypeNode $stmt, int $max, int $min = 0): void
    {
        if ($stmt->arguments === null || $stmt->arguments->count() <= $max) {
            return;
        }

        throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
            minSupportedArgumentsCount: $min,
            maxSupportedArgumentsCount: $max,
            type: $stmt,
        );
    }

    /**
     * @api
     *
     * @param int<0, max> $min
     * @param int<0, max>|null $max
     *
     * @throws MissingTemplateArgumentsException
     */
    protected static function expectTemplateArgumentsGreaterThan(NamedTypeNode $stmt, int $min, ?int $max = null): void
    {
        static::expectTemplateArgumentsGreaterOrEqualThan($stmt, $min + 1, $max);
    }

    /**
     * @api
     *
     * @param int<0, max> $min
     * @param int<0, max>|null $max
     *
     * @throws MissingTemplateArgumentsException
     */
    protected static function expectTemplateArgumentsGreaterOrEqualThan(NamedTypeNode $stmt, int $min, ?int $max = null): void
    {
        $actualArgumentsCount = $stmt->arguments?->count() ?? 0;

        if ($actualArgumentsCount >= $min) {
            return;
        }

        throw MissingTemplateArgumentsException::becauseTemplateArgumentsRangeRequired(
            minSupportedArgumentsCount: $min,
            maxSupportedArgumentsCount: $max ?? $min,
            type: $stmt,
        );
    }

    /**
     * @api
     *
     * @throws TemplateArgumentHintsNotSupportedException
     */
    protected static function expectNoTemplateArgumentHint(TypeStatement $stmt, TemplateArgumentNode $argument): void
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
