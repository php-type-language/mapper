<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Shape;

use TypeLang\Mapper\Exception\Definition\DefinitionException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

/**
 * An exception associated with ALL possible shape fields.
 */
abstract class ShapeFieldsException extends DefinitionException
{
    public function __construct(NamedTypeNode $type, string $template, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($type, $template, $code, $previous);
    }
}
