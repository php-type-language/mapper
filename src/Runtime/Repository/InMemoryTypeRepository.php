<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class InMemoryTypeRepository extends TypeRepositoryDecorator
{
    /**
     * @var \WeakMap<TypeStatement, TypeInterface>
     */
    private readonly \WeakMap $types;

    public function __construct(TypeRepositoryInterface $delegate)
    {
        $this->types = new \WeakMap();

        parent::__construct($delegate);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement): TypeInterface
    {
        // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
        return $this->types[$statement] ??= parent::getTypeByStatement($statement);
    }
}
