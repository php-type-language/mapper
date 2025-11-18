<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository;

use TypeLang\Mapper\Kernel\Repository\TypeDecorator\TraceableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Printer\PrinterInterface as TypePrinterInterface;

final class DecorateByTraceableTypeRepository extends TypeRepositoryDecorator
{
    public function __construct(
        private readonly bool $enableTypeMatchTracing,
        private readonly bool $enableTypeCastTracing,
        private readonly TypePrinterInterface $printer,
        TypeRepositoryInterface $delegate,
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
            traceTypeMatching: $this->enableTypeMatchTracing,
            traceTypeCasting: $this->enableTypeCastTracing,
            definition: $this->printer->print($statement),
            delegate: $type,
        );
    }
}
