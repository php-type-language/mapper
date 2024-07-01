<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

final class TypeNotCreatableException extends TypeException
{
    public static function fromTypeName(string $name, ?\Throwable $prev = null): self
    {
        $message = \sprintf('Type %s cannot be created', StringInfo::quoted($name));

        return new self($message, previous: $prev);
    }
}
