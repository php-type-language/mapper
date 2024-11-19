<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;

class InvalidIterableKeyException extends IterableKeyException
{
    /**
     * @param int<0, max> $index
     * @param iterable<mixed, mixed> $value
     */
    public static function createFromPath(
        int $index,
        mixed $key,
        iterable $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'The key {{key}} on index {{index}} in {{value}} is invalid';

        return new self(
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
        int $index,
        mixed $key,
        iterable $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            index: $index,
            key: $key,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
