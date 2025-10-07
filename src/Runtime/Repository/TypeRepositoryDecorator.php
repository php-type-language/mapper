<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class TypeRepositoryDecorator implements TypeRepositoryDecoratorInterface
{
    public function __construct(
        private readonly TypeRepositoryInterface $delegate,
    ) {
        $this->setTypeRepository($this);
    }

    /**
     * @internal internal method for passing the root calling context
     */
    public function setTypeRepository(TypeRepositoryInterface $parent): void
    {
        if ($this->delegate instanceof TypeRepositoryDecoratorInterface) {
            $this->delegate->setTypeRepository($parent);
        }
    }

    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        return $this->delegate->getTypeByStatement($statement);
    }
}
