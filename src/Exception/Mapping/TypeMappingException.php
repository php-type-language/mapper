<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Path\PathInterface;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Traverser;

abstract class TypeMappingException extends MappingException
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = parent::CODE_ERROR_LAST;

    public function __construct(
        protected readonly TypeStatement $actual,
        TypeStatement|string $expected,
        PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            expected: $expected,
            path: $path,
            template: $template,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * Returns the type statement that causes the error.
     *
     * @api
     */
    public function getActualType(): TypeStatement
    {
        return $this->actual;
    }

    /**
     * @param \Closure(Name):(Name|null) $transform
     */
    #[\Override]
    public function explain(callable $transform): self
    {
        Traverser::through(
            visitor: new Traverser\TypeMapVisitor($transform(...)),
            nodes: [$this->actual],
        );

        parent::explain($transform);

        return $this;
    }
}
