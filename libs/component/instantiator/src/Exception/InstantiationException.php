<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Exception;

/**
 * Failure of the instantiator, carries the name of the class.
 */
abstract class InstantiationException extends \RuntimeException implements
    InstantiatorExceptionInterface
{
    public function __construct(
        /**
         * Name of the class whose object is created
         */
        public readonly string $class,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
