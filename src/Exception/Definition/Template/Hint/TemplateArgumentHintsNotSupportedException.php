<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template\Hint;

use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * Occurs when a type's template argument does not hints
 */
class TemplateArgumentHintsNotSupportedException extends TemplateArgumentHintException
{
    public static function becauseTemplateArgumentHintsNotSupported(
        TemplateArgumentNode $argument,
        TypeStatement $type,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Template argument #{{index}} ({{argument}}) of "{{type}}" does not support any hints, '
            . 'but "{{hint}}" were passed';

        return new self(
            argument: $argument,
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
