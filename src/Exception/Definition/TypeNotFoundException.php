<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

use TypeLang\Parser\Node\Stmt\TypeStatement;

class TypeNotFoundException extends DefinitionException
{
    public static function becauseTypeNotDefined(TypeStatement $type, ?\Throwable $previous = null): self
    {
        return new self(
            type: $type,
            template: 'Type "{{type}}" is not registered',
            previous: $previous,
        );
    }
}
