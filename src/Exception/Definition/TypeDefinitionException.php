<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception\Definition;

/**
 * @phpstan-consistent-constructor
 */
abstract class TypeDefinitionException extends \InvalidArgumentException implements
    TypeDefinitionExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
