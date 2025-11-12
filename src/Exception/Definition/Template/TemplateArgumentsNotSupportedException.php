<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * Occurs when a type does not support template arguments
 */
class TemplateArgumentsNotSupportedException extends TemplateArgumentsCountException
{
    public static function becauseTooManyArguments(
        NamedTypeNode $type,
        ?\Throwable $previous = null
    ): self {
        $passedArgumentsCount = $type->arguments?->count() ?? 0;

        assert($passedArgumentsCount > 0, new \InvalidArgumentException(
            'Semantic Violation: Passed type should contain template arguments',
        ));

        $template = 'Type "{{type}}" does not support template arguments, '
            . 'but {{passedArgumentsCount}} were passed';

        return new self(
            passedArgumentsCount: $passedArgumentsCount,
            expectedArgumentsCount: 0,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
