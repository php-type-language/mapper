<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

class InvalidTypeNameException extends TypeException
{
    /**
     * @var int<0, max>
     */
    final public const ERROR_CODE_EMPTY_TYPE_NAME = 0x01 + parent::ERROR_CODE_LAST;

    /**
     * @var int<0, max>
     * @psalm-suppress InvalidConstantAssignmentValue
     */
    protected const ERROR_CODE_LAST = self::ERROR_CODE_EMPTY_TYPE_NAME;

    public static function fromEmptyTypeName(): self
    {
        return new self('Type name must not be empty', (int) self::ERROR_CODE_EMPTY_TYPE_NAME);
    }
}
