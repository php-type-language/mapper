<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
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

    /**
     * @param TStmt $stmt
     * @param int<0, max> $max
     * @param int<0, max>|null $min
     *
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     * @throws TooManyTemplateArgumentsException
     */
    protected static function assertTemplateArgumentsCount(TypeStatement $stmt, int $max, ?int $min = null): void
    {
        if ($max === 0) {
            self::assertNoTemplateArguments($stmt);

            return;
        }

        if (!$stmt instanceof NamedTypeNode) {
            return;
        }

        $min ??= $max;

        if ($min > $max) {
            [$max, $min] = [$min, $max];
        }

        $actual = $stmt->arguments?->count() ?? 0;

        if ($max < $actual) {
            throw TooManyTemplateArgumentsException::becauseTemplateArgumentsRangeOverflows(
                passedArgumentsCount: $actual,
                minSupportedArgumentsCount: $min,
                maxSupportedArgumentsCount: $max,
                type: $stmt,
            );
        }

        if ($min > $actual) {
            throw MissingTemplateArgumentsException::becauseTemplateArgumentsRangeRequired(
                passedArgumentsCount: $actual,
                minSupportedArgumentsCount: $min,
                maxSupportedArgumentsCount: $max,
                type: $stmt,
            );
        }
    }
}
