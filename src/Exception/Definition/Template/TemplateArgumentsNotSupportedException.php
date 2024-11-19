<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Occurs when a type does not support template arguments
 */
class TemplateArgumentsNotSupportedException extends TemplateArgumentsCountException
{
    /**
     * @param int<0, max> $passedArgumentsCount
     */
    public static function becauseTemplateArgumentsNotSupported(
        int $passedArgumentsCount,
        TypeStatement $type,
        ?\Throwable $previous = null
    ): self {
        $template = 'Type "{{type}}" does not support template arguments, '
            . 'but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            minSupportedArgumentsCount: 0,
            maxSupportedArgumentsCount: 0,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
