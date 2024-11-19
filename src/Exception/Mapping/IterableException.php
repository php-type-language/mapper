<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;

abstract class IterableException extends ValueException implements
    FinalExceptionInterface
{
    /**
     * @param iterable<mixed, mixed> $value
     */
    public function __construct(
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
     * Unlike {@see ValueException::getValue()}, this exception
     * value can only be {@see iterable}.
     *
     * @return iterable<mixed, mixed>
     */
    public function getValue(): iterable
    {
        /** @var iterable<mixed, mixed> */
        return $this->value;
    }
}
