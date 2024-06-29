<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Definition;

class UnsupportedAttributeException extends TypeDefinitionException
{
    public static function fromAttributeName(object $attribute, \Throwable $prev = null): static
    {
        $message = \sprintf('Unsupported attribute %s', $attribute::class);

        return new static($message, previous: $prev);
    }
}
