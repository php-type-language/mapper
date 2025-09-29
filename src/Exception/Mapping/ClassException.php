<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ClassException extends ValueOfTypeException implements
    FinalExceptionInterface
{
    /**
     * @param class-string $class
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

    /**
     * Unlike {@see ValueException::getClass()}, this method must return
     * only {@see class-string}.
     *
     * @return class-string
     */
    public function getClass(): string
    {
        /** @var class-string */
        return $this->value;
    }
}
