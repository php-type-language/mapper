<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Runtime\Repository\TypeDecorator\TraceableType;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final class TraceableTypeRepository extends TypeRepositoryDecorator
{
    private readonly TypePrinterInterface $printer;

    public function __construct(
        private readonly TracerInterface $tracer,
        TypeRepositoryInterface $delegate,
    ) {
        parent::__construct($delegate);

        $this->printer = new PrettyPrinter();
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start(\vsprintf('Fetch type "%s"', [
            $this->printer->print($statement),
        ]));

        try {
            $span->setAttribute('value', $statement);

            $result = parent::getTypeByStatement($statement, $context);

            $span->setAttribute('result', $result);
        } finally {
            $span->stop();
        }

        if ($result instanceof TraceableType) {
            return $result;
        }

        return new TraceableType($this->tracer, $result);
    }
}
