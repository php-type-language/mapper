<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TValue of array<array-key, mixed>|object = array<array-key, mixed>|object
 * @template-extends ValueOfTypeException<TValue>
 */
abstract class ObjectException extends ValueOfTypeException implements
    FinalExceptionInterface
{
    /**
     * @param TValue $value unlike {@see ValueException::$value}, this property
     *        must contain only {@see object} or {@see array}
     */
    public function __construct(
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
