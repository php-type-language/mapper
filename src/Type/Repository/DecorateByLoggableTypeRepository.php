<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Type\Repository\TypeDecorator\LoggableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class DecorateByLoggableTypeRepository extends TypeRepositoryDecorator
{
    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        $type = parent::getTypeByStatement($statement);

        if ($type instanceof LoggableType) {
            return $type;
        }

        return new LoggableType($type);
    }
}
