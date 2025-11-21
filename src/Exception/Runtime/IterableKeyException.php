<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Path\PathInterface;

/**
 * @template TValue of mixed = mixed
 *
 * @template-extends IterableException<TValue>
 */
abstract class IterableKeyException extends IterableException
{
    /**
     * @param TValue $value
     */
    public function __construct(
        /**
         * Gets an ordered index of an element.
         *
         * @var int<0, max>
         */
        public readonly int $index,
        /**
         * Gets the real key of the element.
         *
         * Note that the value can be any ({@see mixed}) and may not necessarily
         * be compatible with PHP array keys ({@see int} or {@see string}).
         */
        public readonly mixed $key,
        mixed $value,
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
