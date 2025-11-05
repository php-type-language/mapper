<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\PathInterface;

/**
 * @template TValue of iterable = iterable<mixed, mixed>
 * @template-extends IterableValueException<TValue>
 */
class InvalidIterableValueException extends IterableValueException
{
    /**
     * @template TArgValue of iterable
     *
     * @param int<0, max> $index
     * @param TArgValue $value
     *
     * @return self<TArgValue>
     */
    public static function createFromPath(
        mixed $element,
        int $index,
        mixed $key,
        mixed $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value {{element}} on {{key}} in {{value}} is invalid';

        if (!\is_scalar($key)) {
            $template = \str_replace('{{key}}', '{{key}} (on index {{index}})', $template);
        } elseif (\is_array($value) && \array_is_list($value)) {
            $template = \str_replace('{{key}}', 'index {{index}}', $template);
        }

        /** @var self<TArgValue> */
        return new self(
            element: $element,
            index: $index,
            key: $key,
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @return self<iterable<array-key, mixed>>
     */
    public static function createFromContext(
        mixed $element,
        int $index,
        mixed $key,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        /** @var self<iterable<array-key, mixed>> */
        return self::createFromPath(
            element: $element,
            index: $index,
            key: $key,
            value: $context->value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
