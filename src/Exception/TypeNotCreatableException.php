<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception;

class TypeNotCreatableException extends TypeException
{
    public static function fromTypeName(string $name, ?\Throwable $prev = null): self
    {
        $name = \addcslashes($name, '"');
        $message = \sprintf('Type "%s" cannot be created', $name);

        return new static($message, previous: $prev);
    }
}
