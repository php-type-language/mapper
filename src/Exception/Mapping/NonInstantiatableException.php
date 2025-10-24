<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TObject of object = object
 * @template-extends ClassException<TObject>
 */
class NonInstantiatableException extends ClassException
{
    /**
     * @template TArg of object
     *
     * @param class-string<TArg> $class
     *
     * @return self<TArg>
     */
    public static function createFromPath(
        ?TypeStatement $expected,
        string $class,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Unable to instantiate {{value}} of {{expected}}';

        /** @var self<TArg> */
        return new self(
            expected: $expected ?? self::mixedTypeStatement(),
            class: $class,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @template TArg of object
     *
     * @param class-string<TArg> $class
     *
     * @return self<TArg>
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
