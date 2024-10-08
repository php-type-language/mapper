<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * @deprecated TODO
 */
final class TypeRequiredException extends TypeException
{
    public static function fromInvalidFieldType(
        string $class,
        string $field,
        int $code = 0,
        ?\Throwable $prev = null,
    ): self {
        $message = \vsprintf('The %s::$%s property contains an unregistered type that cannot be explicitly converted', [
            $class,
            $field,
        ]);

        return new self($message, $code, $prev);
    }
}
