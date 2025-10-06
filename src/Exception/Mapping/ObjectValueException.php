<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TValue of array<array-key, mixed>|object = array<array-key, mixed>|object
 * @template-extends ObjectFieldException<non-empty-string, TValue>
 */
abstract class ObjectValueException extends ObjectFieldException
{
    /**
     * @param non-empty-string $field
     * @param TValue $value
     */
    public function __construct(
        public readonly mixed $element,
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
}
