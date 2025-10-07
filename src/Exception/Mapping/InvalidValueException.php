<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;

/**
 * @template TValue of mixed = mixed
 * @template-extends ValueException<TValue>
 */
class InvalidValueException extends ValueException
{
    /**
     * @template TArgValue of mixed
     *
     * @param TArgValue $value
     *
     * @return self<TArgValue>
     */
    public static function createFromPath(
        mixed $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value {{value}} is invalid';

        /** @var self<TArgValue> */
        return new self(
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @template TArgValue of mixed
     *
     * @param TArgValue $value
     *
     * @return self<TArgValue>
     */
    public static function createFromContext(
        mixed $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
