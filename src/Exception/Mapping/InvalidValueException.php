<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;

class InvalidValueException extends ValueException
{
    public static function createFromPath(
        mixed $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value {{value}} is invalid';

        return new self(
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

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
