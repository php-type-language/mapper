<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

class TypeException extends \InvalidArgumentException implements
    MapperExceptionInterface
{
    /**
     * @var int<0, max>
     */
    final public const ERROR_CODE_INTERNAL_ERROR = 0x01;

    /**
     * @var int<0, max>
     */
    final public const ERROR_CODE_INTERNAL_EXCEPTION = 0x02;

    /**
     * @var int<0, max>
     */
    protected const ERROR_CODE_LAST = self::ERROR_CODE_INTERNAL_EXCEPTION;

    final public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromInternalParsingError(string $type): self
    {
        $message = 'Internal error, parser returned NULL while processing the type "%s"';
        $message = \sprintf($message, $type);

        return new static($message, self::ERROR_CODE_INTERNAL_ERROR);
    }

    public static function fromInternalParsingException(string $type, \Throwable $e): self
    {
        $message = 'Cannot parse type "%s": %s';
        $message = \sprintf($message, $type, $e->getMessage());

        return new static($message, self::ERROR_CODE_INTERNAL_EXCEPTION, $e);
    }
}
