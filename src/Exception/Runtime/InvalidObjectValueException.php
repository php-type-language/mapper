<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TValue of array<array-key, mixed>|object = array<array-key, mixed>|object
 * @template-extends ObjectValueException<TValue>
 */
class InvalidObjectValueException extends ObjectValueException
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
        mixed $element,
        string $field,
        ?TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value in {{field}} of {{value}} must be of type {{expected}}, but {{element}} given';

        /** @var self<TArgValue> */
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
     * @return self<array<array-key, mixed>|object>
     */
    public static function createFromContext(
        mixed $element,
        string $field,
        ?TypeStatement $expected,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        /** @var array<array-key, mixed>|object $value */
        $value = $context->value;

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
