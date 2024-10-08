<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition\Shape;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class ShapeFieldsNotSupportedException extends ShapeFieldsException
{
    /**
     * @var int
     */
    public const CODE_ERROR_SHAPE_FIELDS_NOT_SUPPORTED = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_SHAPE_FIELDS_NOT_SUPPORTED;

    public static function becauseShapeFieldsNotSupported(
        TypeStatement $type,
        ?\Throwable $previous = null
    ): self {
        $template = 'Type "{{type}}" does not support shape fields';

        return new self(
            type: $type,
            template: $template,
            code: self::CODE_ERROR_SHAPE_FIELDS_NOT_SUPPORTED,
            previous: $previous,
        );
    }
}
