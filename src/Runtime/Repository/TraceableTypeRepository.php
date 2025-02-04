<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeDecorator\TraceableType;
use TypeLang\Mapper\Runtime\Tracing\TracerInterface;
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

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $span = $this->tracer->start(\vsprintf('Fetch type "%s"', [
            $this->printer->print($statement),
        ]));

        try {
            $result = parent::getTypeByStatement($statement, $context);
        } finally {
            $span->stop();
        }

        if ($result instanceof TraceableType) {
            return $result;
        }

        return new TraceableType(
            definition: $this->printer->print($statement),
            tracer: $this->tracer,
            delegate: $result,
        );
    }
}
