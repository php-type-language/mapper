<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class MissingRequiredObjectFieldException extends ObjectFieldException
{
    /**
     * @param array<array-key, mixed>|object $value
     */
    public static function createFromPath(
        mixed $field,
        TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Object {{value}} requires missing field {{field}} of type {{expected}}';

        return new self(
            field: $field,
            expected: $expected,
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param iterable<mixed, mixed> $value
     */
    public static function createFromContext(
        mixed $field,
        TypeStatement $expected,
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
