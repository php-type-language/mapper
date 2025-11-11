<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Template;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * An exception associated with ALL possible template arguments.
 */
abstract class TemplateArgumentsException extends DefinitionException
{
    public function __construct(NamedTypeNode $type, string $template, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($type, $template, $code, $previous);
    }
}
