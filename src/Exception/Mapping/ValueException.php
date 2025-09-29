<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;

abstract class ValueException extends RuntimeException
{
    public function __construct(
        protected readonly mixed $value,
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

    /**
     * Returns the value that causes the error.
     */
    public function getClass(): mixed
    {
        return $this->value;
    }
}
