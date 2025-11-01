<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Type\Repository\TypeDecorator\TraceableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final class DecorateByTraceableTypeRepository extends TypeRepositoryDecorator
{
    public function __construct(
        TypeRepositoryInterface $delegate,
        private readonly TypePrinterInterface $printer = new PrettyPrinter(
            wrapUnionType: false,
            multilineShape: \PHP_INT_MAX,
        ),
    ) {
        parent::__construct($delegate);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $type = parent::getTypeByStatement($statement);

        if ($type instanceof TraceableType) {
            return $type;
        }

        return new TraceableType(
            definition: $this->printer->print($statement),
            delegate: $type,
        );
    }
}
