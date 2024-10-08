<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\TypeException;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Traverser;
use TypeLang\Parser\Traverser\TypeMapVisitor;

/**
 * @deprecated TODO
 */
abstract class MappingException extends TypeException implements MappingExceptionInterface
{
    /**
     * @param list<non-empty-string|int> $path
     */
    public function __construct(
        string $template,
        private TypeStatement $expectedType,
        private array $path = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($template, $code, $previous);
    }

    /**
     * @param \Closure(Name):(Name|null) $transform
     */
    public function explain(callable $transform): static
    {
        Traverser::through(
            visitor: new TypeMapVisitor($transform(...)),
            nodes: [$this->expectedType],
        );

        $this->updateMessage();

        return $this;
    }

    /**
     * @return array<non-empty-string, string|TypeStatement>
     */
    protected function getReplacements(): array
    {
        return [
            ...parent::getReplacements(),
            'expected' => $this->expectedType,
            'path' => $this->getPathAsString(),
        ];
    }

    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @api
     *
     * @param list<non-empty-string|int> $path
     *
     * @return $this
     */
    public function setPath(array $path): self
    {
        $this->path = $path;
        $this->updateMessage();

        return $this;
    }

    public function getPathAsString(): string
    {
        $result = '$';

        foreach ($this->getPath() as $segment) {
            $result .= \is_string($segment)
                ? ".$segment"
                : "[$segment]";
        }

        if ($result === '$') {
            return 'root';
        }

        return $result;
    }

    public function getExpectedType(): TypeStatement
    {
        return $this->expectedType;
    }

    /**
     * @api
     *
     * @return $this
     */
    public function setExpectedType(TypeStatement $type): self
    {
        $this->expectedType = $type;
        $this->updateMessage();

        return $this;
    }
}
