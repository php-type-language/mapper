<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception\Mapping;

use TypeLang\Mapper\Exception\StringInfo;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\Traverser;
use TypeLang\Parser\Traverser\TypeMapVisitor;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

abstract class MappingException extends \RuntimeException implements MappingExceptionInterface
{
    private PrinterInterface $printer;

    /**
     * @param list<non-empty-string|int> $path
     */
    public function __construct(
        private readonly string $template,
        private TypeStatement $expectedType,
        private array $path = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->printer = new PrettyPrinter();
        $this->printer->multilineShape = \PHP_INT_MAX;

        $message = $this->updateMessage();

        parent::__construct($message, $code, $previous);
    }

    /**
     * @api
     *
     * @return $this
     */
    public function setTypePrinter(PrinterInterface $printer): self
    {
        $this->printer = $printer;
        $this->updateMessage();

        return $this;
    }

    /**
     * @param \Closure(Name):(Name|null) $transform
     */
    public function explain(callable $transform): self
    {
        Traverser::through(
            visitor: new TypeMapVisitor($transform(...)),
            nodes: [$this->expectedType],
        );

        $this->updateMessage();

        return $this;
    }

    protected function updateMessage(): string
    {
        return $this->message = $this->render($this->template);
    }

    private function render(string $template): string
    {
        foreach ($this->getReplacements() as $from => $to) {
            if ($to instanceof TypeStatement) {
                $to = $this->printer->print($to);
            }

            $template = \str_replace('{{' . $from . '}}', $to, $template);
        }

        return $template;
    }

    /**
     * @return array<non-empty-string, string>
     */
    protected function getReplacements(): array
    {
        return [
            'expected' => $this->expectedType,
            'path' => ($path = $this->getPathAsString()) === '' ? 'root' : StringInfo::quoted($path),
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
        return \implode('.', $this->getPath());
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
