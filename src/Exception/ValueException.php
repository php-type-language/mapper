<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

class ValueException extends \InvalidArgumentException implements
    MapperExceptionInterface
{
    /**
     * @var int<0, max>
     */
    protected const ERROR_CODE_LAST = 0x00;

    final public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
