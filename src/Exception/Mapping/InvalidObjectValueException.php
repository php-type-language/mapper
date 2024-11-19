<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class InvalidObjectValueException extends ObjectValueException
{
    /**
     * @param non-empty-string $field
     * @param array<array-key, mixed>|object $value
     */
    public static function createFromPath(
        mixed $element,
        string $field,
        ?TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value in {{field}} of {{value}} must be of type {{expected}}, but {{element}} given';

        return new self(
            element: $element,
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
     * @param array<array-key, mixed>|object $value
     */
    public static function createFromContext(
        mixed $element,
        string $field,
        ?TypeStatement $expected,
        array|object $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            element: $element,
            field: $field,
            expected: $expected,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
