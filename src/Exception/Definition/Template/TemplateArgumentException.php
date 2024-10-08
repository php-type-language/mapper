<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * An exception associated with ONE specific template argument.
 */
abstract class TemplateArgumentException extends TemplateArgumentsException
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = parent::CODE_ERROR_LAST;

    public function __construct(
        protected readonly TemplateArgumentNode $argument,
        TypeStatement $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($type, $template, $code, $previous);
    }

    /**
     * @api
     */
    public function getArgument(): TemplateArgumentNode
    {
        return $this->argument;
    }
}
