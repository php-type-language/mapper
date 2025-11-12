<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template\Hint;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;

/**
 * Occurs when a type's template argument does not support hints
 */
class TemplateArgumentHintNotSupportedException extends TemplateArgumentHintException
{
    public static function becauseTooManyHints(
        TemplateArgumentNode $argument,
        NamedTypeNode $type,
        ?\Throwable $previous = null
    ): self {
        $template = 'Template argument #{{index}} ({{argument}}) of "{{type}}" does not support any hints, '
            . 'but "{{hint}}" were passed';

        assert($argument->hint !== null, new \InvalidArgumentException(
            'Semantic Violation: Argument should contain argument hint',
        ));

        return new self(
            argument: $argument,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
