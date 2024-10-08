<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class TypeNotFoundException extends DefinitionException
{
    /**
     * @var int
     */
    public const CODE_ERROR_TYPE_NOT_DEFINED = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_TYPE_NOT_DEFINED;

    public static function becauseTypeNotDefined(TypeStatement $type, ?\Throwable $previous = null): self
    {
        return new self(
            type: $type,
            template: 'Type "{{type}}" is not registered',
            code: self::CODE_ERROR_TYPE_NOT_DEFINED,
            previous: $previous,
        );
    }
}
