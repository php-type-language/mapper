<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Runtime\Path\PathInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class ValueOfTypeException extends ValueException
{
    public function __construct(
        protected readonly TypeStatement $expected,
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

    /**
     * Returns the type statement in which the error occurred.
     *
     * @api
     */
    public function getExpectedType(): TypeStatement
    {
        return $this->expected;
    }
}
