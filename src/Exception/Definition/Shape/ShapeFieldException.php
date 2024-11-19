<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Shape;

use TypeLang\Parser\Node\Stmt\Shape\FieldNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * An exception associated with ONE specific shape field.
 */
abstract class ShapeFieldException extends ShapeFieldsException
{
    public function __construct(
        protected readonly FieldNode $field,
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
    public function getField(): FieldNode
    {
        return $this->field;
    }
}
