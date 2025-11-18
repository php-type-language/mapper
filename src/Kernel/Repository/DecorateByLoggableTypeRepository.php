<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository;

use TypeLang\Mapper\Kernel\Repository\TypeDecorator\LoggableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DecorateByLoggableTypeRepository extends TypeRepositoryDecorator
{
    public function __construct(
        private readonly bool $enableTypeMatchLogging,
        private readonly bool $enableTypeCastLogging,
        TypeRepositoryInterface $delegate,
    ) {
        parent::__construct($delegate);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $type = parent::getTypeByStatement($statement);

        if ($type instanceof LoggableType) {
            return $type;
        }

        return new LoggableType(
            logTypeMatching: $this->enableTypeMatchLogging,
            logTypeCasting: $this->enableTypeCastLogging,
            delegate: $type,
        );
    }
}
