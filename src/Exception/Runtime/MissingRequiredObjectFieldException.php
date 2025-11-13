<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TValue of array<array-key, mixed>|object = array<array-key, mixed>|object
 * @template-extends ObjectFieldException<non-empty-string, TValue>
 */
class MissingRequiredObjectFieldException extends ObjectFieldException
{
    /**
     * @template TArgValue of array|object
     *
     * @param non-empty-string $field
     * @param TArgValue $value
     *
     * @return self<TArgValue>
     */
    public static function createFromPath(
        string $field,
        ?TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Object {{value}} requires missing field {{field}} of type {{expected}}';

        /** @var self<TArgValue> */
        return new self(
            field: $field,
            expected: $expected ?? self::mixedTypeStatement(),
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param non-empty-string $field
     *
     * @return self<array<array-key, mixed>|object>
     */
    public static function createFromContext(
        string $field,
        ?TypeStatement $expected,
        RuntimeContext $context,
        ?\Throwable $previous = null,
    ): self {
        /** @var array<array-key, mixed>|object $value */
        $value = $context->value;

        return self::createFromPath(
            field: $field,
            expected: $expected,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
