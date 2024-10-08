<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ValueMappingException extends TypeMappingException
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = parent::CODE_ERROR_LAST;

    public function __construct(
        protected readonly mixed $value,
        TypeStatement $expected,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            // @phpstan-ignore-next-line
            actual: new NamedTypeNode(\get_debug_type($value)),
            expected: $expected,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * Returns the value that causes the error.
     *
     * @api
     */
    public function getActualValue(): mixed
    {
        return $this->value;
    }
}
