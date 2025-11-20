<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Runtime;

use TypeLang\Mapper\Context\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template TObject of object = object
 *
 * @template-extends ValueOfTypeException<class-string<TObject>>
 */
abstract class ClassException extends ValueOfTypeException implements
    NotInterceptableExceptionInterface
{
    /**
     * @param class-string<TObject> $class unlike {@see ValueException::$value},
     *        this property must contain only {@see class-string}
     */
    public function __construct(
        TypeStatement $expected,
        string $class,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            expected: $expected,
            value: $class,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }
}
