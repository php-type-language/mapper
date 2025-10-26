<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TField of mixed = mixed
 * @template TValue of array<array-key, mixed>|object = array<array-key, mixed>|object
 * @template-extends ObjectException<TValue>
 */
abstract class ObjectFieldException extends ObjectException
{
    /**
     * @param TValue $value
     */
    public function __construct(
        /**
         * Gets the field of an object-like value.
         *
         * Note that the value can be any ({@see mixed}) and may not necessarily
         * be compatible with PHP array keys ({@see int} or {@see string}).
         *
         * @var TField
         */
        public readonly mixed $field,
        TypeStatement $expected,
        array|object $value,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            expected: $expected,
            value: $value,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }
}
