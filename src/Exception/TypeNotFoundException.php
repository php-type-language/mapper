<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

class TypeNotFoundException extends TypeException
{
    public static function fromTypeName(string $name, ?\Throwable $prev = null): self
    {
        $message = \sprintf('Type "%s" is not registered', $name);

        return new static($message, previous: $prev);
    }
}
