<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

/**
 * @phpstan-consistent-constructor
 */
class TypeException extends \LogicException implements MapperExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
