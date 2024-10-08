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
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = parent::CODE_ERROR_LAST;

    public function __construct(
        protected readonly Identifier $hint,
        TemplateArgumentNode $argument,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($argument, $type, $template, $code, $previous);
    }

    /**
     * @api
     */
    public function getArgumentHint(): Identifier
    {
        return $this->hint;
    }
}
