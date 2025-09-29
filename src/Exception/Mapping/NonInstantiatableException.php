<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

class NonInstantiatableException extends ClassException
{
    /**
     * @param class-string $class
     */
    public static function createFromPath(
        ?TypeStatement $expected,
        string $class,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Unable to instantiate {{value}} of {{expected}}';

        return new self(
            expected: $expected ?? self::mixedTypeStatement(),
            class: $class,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @param class-string $class
     */
    public static function createFromContext(
        ?TypeStatement $expected,
        string $class,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            expected: $expected,
            class: $class,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
