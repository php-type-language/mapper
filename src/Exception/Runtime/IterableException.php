<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Path\PathInterface;

/**
 * @template TValue of iterable = iterable<mixed, mixed>
 * @template-extends ValueException<TValue>
 */
abstract class IterableException extends ValueException implements
    FinalExceptionInterface
{
    /**
     * @param TValue $value unlike {@see ValueException::$value}, this exception
     *        value can only be {@see iterable}
     */
    public function __construct(
        iterable $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            value: $value,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }
}
