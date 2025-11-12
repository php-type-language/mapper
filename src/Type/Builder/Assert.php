<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Builder;

use TypeLang\Mapper\Exception\Definition\Shape\ShapeFieldsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsInRangeException;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsInRangeException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;

final class Assert
{
    /**
     * @throws ShapeFieldsNotSupportedException
     */
    public static function expectNoShapeFields(NamedTypeNode $stmt): void
    {
        if ($stmt->fields === null) {
            return;
        }

        throw ShapeFieldsNotSupportedException::becauseTooManyShapeFields($stmt);
    }

    /**
     * @throws TemplateArgumentsNotSupportedException
     */
    public static function expectNoTemplateArguments(NamedTypeNode $stmt): void
    {
        if ($stmt->arguments === null) {
            return;
        }

        throw TemplateArgumentsNotSupportedException::becauseTooManyArguments($stmt);
    }

    /**
     * @throws TooManyTemplateArgumentsException
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     * @throws TooManyTemplateArgumentsInRangeException
     * @throws MissingTemplateArgumentsInRangeException
     */
    public static function expectTemplateArgumentsCountInRange(NamedTypeNode $stmt, int $from, int $to): void
    {
        $from = \max(0, $from);
        $to = \max(0, $to);

        if ($from === $to) {
            self::expectTemplateArgumentsCount($stmt, $from);

            return;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        if ($from < 1) {
            self::expectTemplateArgumentsCountLessOrEqualThan($stmt, $to);

            return;
        }

        $actualArgumentsCount = $stmt->arguments?->count() ?? 0;

        /** @var int<1, max> $to */
        if ($actualArgumentsCount > $to) {
            throw TooManyTemplateArgumentsInRangeException::becauseArgumentsCountRequired($from, $to, $stmt);
        }

        if ($actualArgumentsCount < $from) {
            throw MissingTemplateArgumentsInRangeException::becauseArgumentsCountRequired($from, $to, $stmt);
        }
    }

    /**
     * @throws TooManyTemplateArgumentsException
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     */
    public static function expectTemplateArgumentsCount(NamedTypeNode $stmt, int $count): void
    {
        if ($count < 1) {
            self::expectNoTemplateArguments($stmt);

            return;
        }

        if (($stmt->arguments?->count() ?? 0) > $count) {
            throw TooManyTemplateArgumentsException::becauseArgumentsCountRequired($count, $stmt);
        }

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired($count, $stmt);
    }

    /**
     * @throws TooManyTemplateArgumentsException
     */
    public static function expectTemplateArgumentsCountGreaterThan(NamedTypeNode $stmt, int $count): void
    {
        self::expectTemplateArgumentsCountGreaterOrEqualThan($stmt, $count + 1);
    }

    /**
     * @throws TooManyTemplateArgumentsException
     */
    public static function expectTemplateArgumentsCountGreaterOrEqualThan(NamedTypeNode $stmt, int $count): void
    {
        $actualArgumentsCount = $stmt->arguments?->count() ?? 0;

        if ($count <= $actualArgumentsCount) {
            return;
        }

        throw TooManyTemplateArgumentsException::becauseArgumentsCountLessThan($count, $stmt);
    }

    /**
     * @throws MissingTemplateArgumentsException
     */
    public static function expectTemplateArgumentsCountLessThan(NamedTypeNode $stmt, int $count): void
    {
        self::expectTemplateArgumentsCountLessOrEqualThan($stmt, $count - 1);
    }

    /**
     * @throws MissingTemplateArgumentsException
     * @throws TemplateArgumentsNotSupportedException
     */
    public static function expectTemplateArgumentsCountLessOrEqualThan(NamedTypeNode $stmt, int $count): void
    {
        if ($count < 1) {
            self::expectNoTemplateArguments($stmt);

            return;
        }

        $actualArgumentsCount = $stmt->arguments?->count() ?? 0;

        if ($count >= $actualArgumentsCount) {
            return;
        }

        throw MissingTemplateArgumentsException::becauseArgumentsCountLessThan($count, $stmt);
    }

    /**
     * @throws TemplateArgumentHintNotSupportedException
     */
    public static function expectNoTemplateArgumentHints(NamedTypeNode $stmt, TemplateArgumentNode $argument): void
    {
        // Skip in case of argument is not a part of type
        if (!\in_array($argument, $stmt->arguments->items ?? [], true)) {
            return;
        }

        if ($argument->hint === null) {
            return;
        }

        throw TemplateArgumentHintNotSupportedException::becauseTooManyHints(
            argument: $argument,
            type: $stmt,
        );
    }

    /**
     * @throws TemplateArgumentHintNotSupportedException
     */
    public static function expectNoAnyTemplateArgumentsHints(NamedTypeNode $stmt): void
    {
        foreach ($stmt->arguments->items ?? [] as $argument) {
            self::expectNoTemplateArgumentHints($stmt, $argument);
        }
    }
}
