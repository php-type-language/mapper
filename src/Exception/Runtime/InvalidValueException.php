<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\PathInterface;

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
     * @return self<mixed>
     */
    public static function createFromContext(
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            value: $context->value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
