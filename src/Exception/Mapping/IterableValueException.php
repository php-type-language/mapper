<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;

abstract class IterableValueException extends IterableKeyException
{
    /**
     * @param int<0, max> $index
     * @param iterable<mixed, mixed> $value
     */
    public function __construct(
        protected readonly mixed $element,
        int $index,
        mixed $key,
        iterable $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            index: $index,
            key: $key,
            value: $value,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    public function getElement(): mixed
    {
        return $this->element;
    }
}
