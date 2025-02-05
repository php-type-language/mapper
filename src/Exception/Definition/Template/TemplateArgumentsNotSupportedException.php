<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type does not support template arguments
 */
class TemplateArgumentsNotSupportedException extends TemplateArgumentsCountException
{
    public static function becauseTemplateArgumentsNotSupported(
        NamedTypeNode $type,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Type "{{type}}" does not support template arguments, '
            . 'but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $type->arguments?->count() ?? 0,
            minSupportedArgumentsCount: 0,
            maxSupportedArgumentsCount: 0,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
