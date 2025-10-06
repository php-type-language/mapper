<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Context;
use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TValue of mixed = mixed
 *
 * @template-extends ValueOfTypeException<TValue>
 */
class InvalidValueOfTypeException extends ValueOfTypeException implements
    FinalExceptionInterface
{
    /**
     * @template TArgValue of mixed
     * @param TArgValue $value
     * @return self<TArgValue>
     */
    public static function createFromPath(
        TypeStatement $expected,
        mixed $value,
        PathInterface $path,
        ?\Throwable $previous = null,
    ): self {
        $template = 'Passed value must be of type {{expected}}, but {{value}} given';

        /** @var self<TArgValue> */
        return new self(
            expected: $expected,
            value: $value,
            path: $path,
            template: $template,
            previous: $previous,
        );
    }

    /**
     * @template TArgValue of mixed
     * @param TArgValue $value
     * @return self<TArgValue>
     */
    public static function createFromContext(
        TypeStatement $expected,
        mixed $value,
        Context $context,
        ?\Throwable $previous = null,
    ): self {
        return self::createFromPath(
            expected: $expected,
            value: $value,
            path: $context->getPath(),
            previous: $previous,
        );
    }
}
