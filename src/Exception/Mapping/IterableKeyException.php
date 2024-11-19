<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;

abstract class IterableKeyException extends IterableException
{
    /**
     * @param int<0, max> $index
     * @param iterable<mixed, mixed> $value
     */
    public function __construct(
        protected readonly int $index,
        protected readonly mixed $key,
        iterable $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            value: $value,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * Returns ordered index of an element.
     *
     * @return int<0, max>
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * Returns the real key of the element.
     *
     * Note that the value can be any ({@see mixed}) and may not necessarily
     * be compatible with PHP array keys ({@see int} or {@see string}).
     */
    public function getKey(): mixed
    {
        return $this->key;
    }
}
