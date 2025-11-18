<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository;

use TypeLang\Mapper\Kernel\Tracing\TracerInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final class TraceableTypeRepository extends TypeRepositoryDecorator
{
    public function __construct(
        private readonly TracerInterface $tracer,
        TypeRepositoryInterface $delegate,
        private readonly TypePrinterInterface $printer = new PrettyPrinter(
            wrapUnionType: false,
            multilineShape: \PHP_INT_MAX,
        ),
    ) {
        parent::__construct($delegate);
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

        return $result;
    }
}
