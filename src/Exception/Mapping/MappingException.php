<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\MapperExceptionInterface;
use TypeLang\Mapper\Exception\Template;
use TypeLang\Mapper\Path\PathInterface;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * An exception that occurs in case of errors during mapping process.
 */
abstract class MappingException extends \RuntimeException implements MapperExceptionInterface
{
    /**
     * @var int
     */
    protected const CODE_ERROR_LAST = 0x00;

    public readonly Template $template;

    protected readonly TypeStatement $expected;

    /**
     * @param TypeStatement|non-empty-string $expected
     */
    public function __construct(
        TypeStatement|string $expected,
        protected readonly PathInterface $path,
        string $template,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        if (\is_string($expected)) {
            $expected = new NamedTypeNode($expected);
        }

        $this->expected = $expected;

        parent::__construct($template, $code, $previous);

        $this->message = $this->template = new Template($template, $this);
    }

    /**
     * TODO
     *
     * @param \Closure(Name):(Name|null) $transform
     */
    public function explain(callable $transform): self
    {
        return $this;
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

    /**
     * Returns the path to the field where the error occurred.
     *
     * @api
     */
    public function getPath(): PathInterface
    {
        return $this->path;
    }
}
