<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

/**
 * @phpstan-consistent-constructor
 */
abstract class TypeCreationException extends \LogicException implements TypeCreationExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
