<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Shape;

use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Shape\FieldNode;

/**
 * An exception associated with ONE specific shape field.
 */
abstract class ShapeFieldException extends ShapeFieldsException
{
    public function __construct(
        public readonly FieldNode $field,
        NamedTypeNode $type,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($type, $template, $code, $previous);
    }
}
