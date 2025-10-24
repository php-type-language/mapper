<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TValue of mixed = mixed
 * @template-extends ValueException<TValue>
 */
abstract class ValueOfTypeException extends ValueException
{
    /**
     * @param TValue $value
     */
    public function __construct(
        /**
         * Gets the type statement in which the error occurred.
         */
        public readonly TypeStatement $expected,
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

    protected static function mixedTypeStatement(): TypeStatement
    {
        return new NamedTypeNode('mixed');
    }
}
