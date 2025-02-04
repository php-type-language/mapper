<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeRepositoryInterface
{
    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable in case of any internal error occurs
     */
    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface;
}
