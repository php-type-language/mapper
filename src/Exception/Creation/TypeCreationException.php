<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Creation;

use TypeLang\Mapper\Exception\MapperExceptionInterface;

/**
 * @phpstan-consistent-constructor
 * @deprecated TODO
 */
abstract class TypeCreationException extends \LogicException implements MapperExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
