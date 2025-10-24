<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Context\Context;
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
     * @template TArgValue of array|object
     *
     * @param non-empty-string $field
     * @param TArgValue $value
     *
     * @return self<TArgValue>
     */
    public static function createFromContext(
        string $field,
        ?TypeStatement $expected,
        array|object $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            field: $field,
            expected: $expected,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
