<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ObjectValueException extends ObjectFieldException
{
    /**
     * @param non-empty-string $field
     * @param array<array-key, mixed>|object $value
     */
    public function __construct(
        protected readonly mixed $element,
        string $field,
        TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            field: $field,
            expected: $expected,
            value: $value,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * Unlike {@see ObjectFieldException::getField()}, method
     * must return only non-empty {@see string}.
     *
     * @return non-empty-string
     */
    public function getField(): string
    {
        return $this->field;
    }

    public function getElement(): mixed
    {
        return $this->element;
    }
}
