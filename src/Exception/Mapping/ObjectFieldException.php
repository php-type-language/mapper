<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ObjectFieldException extends ObjectException
{
    /**
     * @param iterable<mixed, mixed> $value
     */
    public function __construct(
        protected readonly mixed $field,
        TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            expected: $expected,
            value: $value,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * Returns the field of an object-like value.
     *
     * Note that the value can be any ({@see mixed}) and may not necessarily
     * be compatible with PHP array keys ({@see int} or {@see string}).
     */
    public function getField(): mixed
    {
        return $this->field;
    }
}
