<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;

class InvalidIterableValueException extends IterableValueException
{
    /**
     * @param int<0, max> $index
     * @param iterable<mixed, mixed> $value
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
     * @param int<0, max> $index
     * @param iterable<mixed, mixed> $value
     */
    public static function createFromContext(
        mixed $element,
        int $index,
        mixed $key,
        mixed $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            element: $element,
            index: $index,
            key: $key,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
