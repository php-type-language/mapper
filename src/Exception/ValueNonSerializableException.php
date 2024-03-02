<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

class ValueNonSerializableException extends ValueException
{
    /**
     * @var int<0, max>
     */
    final public const ERROR_CODE_VALUE_NOT_SERIALIZABLE = 0x01;

    /**
     * @var int<0, max>
     * @psalm-suppress InvalidConstantAssignmentValue
     */
    protected const ERROR_CODE_LAST = self::ERROR_CODE_VALUE_NOT_SERIALIZABLE;

    public static function fromInvalidValue(mixed $value): self
    {
        $message = 'Cannot serialize the value of type "%s"';
        $message = \sprintf($message, \get_debug_type($value));

        return new static($message, self::ERROR_CODE_VALUE_NOT_SERIALIZABLE);
    }
}
