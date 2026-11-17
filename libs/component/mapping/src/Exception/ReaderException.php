<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Exception;

/**
 * Failure to read metadata of a class.
 */
abstract class ReaderException extends \RuntimeException
{
    protected function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
