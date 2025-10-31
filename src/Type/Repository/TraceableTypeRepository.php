<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Tracing\TracerInterface;
use TypeLang\Mapper\Type\Repository\TypeDecorator\TraceableType;
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

        $this->printer = new PrettyPrinter(
            wrapUnionType: false,
            multilineShape: \PHP_INT_MAX,
        );
    }

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $span = $this->tracer->start(\vsprintf('Fetch type "%s"', [
            $this->printer->print($statement),
        ]));

        try {
            $result = parent::getTypeByStatement($statement);
        } finally {
            $span->stop();
        }

        if ($result instanceof TraceableType) {
            return $result;
        }

        return new TraceableType(
            definition: $this->printer->print($statement),
            delegate: $result,
        );
    }
}
