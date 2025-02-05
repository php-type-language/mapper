<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Shape;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class ShapeFieldsNotSupportedException extends ShapeFieldsException
{
    public static function becauseShapeFieldsNotSupported(
        TypeStatement $type,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Type "{{type}}" does not support shape fields';

        return new self(
            type: $type,
            template: $template,
            previous: $previous,
        );
    }
}
