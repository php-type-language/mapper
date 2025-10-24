<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Context\Path\PathInterface;

/**
 * @template TValue of mixed = mixed
 */
abstract class ValueException extends RuntimeException
{
    public function __construct(
        /**
         * Gets the value that causes the error.
         *
         * @var TValue
         */
        public readonly mixed $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }
}
