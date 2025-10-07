<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template\Hint;

use TypeLang\Parser\Node\Identifier;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * An exception associated with ONE specific template argument hint.
 */
abstract class TemplateArgumentHintException extends TemplateArgumentHintsException
{
    public readonly ?Identifier $hint;

    public function __construct(
        TemplateArgumentNode $argument,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->hint = $argument->hint;

        parent::__construct(
            argument: $argument,
            type: $type,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }
}
