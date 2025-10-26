<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\PathInterface;

/**
 * @template TValue of iterable = iterable<mixed, mixed>
 * @template-extends IterableKeyException<TValue>
 */
class InvalidIterableKeyException extends IterableKeyException
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
        int $index,
        mixed $key,
        iterable $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'The key {{key}} on index {{index}} in {{value}} is invalid';

        /** @var self<TArgValue> */
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
     * @template TArgValue of iterable
     *
     * @param int<0, max> $index
     * @param TArgValue $value
     *
     * @return self<TArgValue>
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
