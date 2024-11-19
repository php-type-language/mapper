<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class InternalTypeException extends DefinitionException
{
    public static function becauseInternalTypeErrorOccurs(
        TypeStatement $type,
        ?string $message = null,
        ?\Throwable $previous = null,
    ): self {
        return new self(
            type: $type,
            template: $message ?? 'An error occurred while creating "{{type}}" type',
            previous: $previous,
        );
    }
}
