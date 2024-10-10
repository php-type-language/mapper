<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class InternalTypeException extends DefinitionException
{
    /**
     * @var int
     */
    public const CODE_ERROR_INTERNAL = 0x01 + parent::CODE_ERROR_LAST;

    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = self::CODE_ERROR_INTERNAL;

    public static function becauseInternalTypeErrorOccurs(
        TypeStatement $type,
        ?string $message = null,
        ?\Throwable $previous = null,
    ): self {
        return new self(
            type: $type,
            template: $message ?? 'An error occurred while creating "{{type}}" type',
            code: self::CODE_ERROR_INTERNAL,
            previous: $previous,
        );
    }
}
