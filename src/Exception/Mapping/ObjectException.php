<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ObjectException extends ValueOfTypeException implements
    FinalExceptionInterface
{
    /**
     * @param array<array-key, mixed>|object $value
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

    protected static function mixedTypeStatement(): TypeStatement
    {
        return new NamedTypeNode('mixed');
    }

    /**
     * Unlike {@see ValueException::getValue()}, this method must return
     * only {@see object} or {@see array}.
     *
     * @return array<array-key, mixed>|object
     */
    public function getValue(): array|object
    {
        return $this->value;
    }
}
