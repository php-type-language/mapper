<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * @phpstan-consistent-constructor
 */
class TypeException extends \LogicException implements MapperExceptionInterface
{
    private PrinterInterface $printer;

    public function __construct(
        protected readonly string $template,
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

    protected function updateMessage(): string
    {
        $template = $this->template;

        foreach ($this->getReplacements() as $from => $to) {
            if ($to instanceof TypeStatement) {
                $to = $this->printer->print($to);
            }

            $template = \str_replace('{{' . $from . '}}', $to, $template);
        }

        return $this->message = $template;
    }

    /**
     * @return array<non-empty-string, string|TypeStatement>
     */
    protected function getReplacements(): array
    {
        return [];
    }
}
