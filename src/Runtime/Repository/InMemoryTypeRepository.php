<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class InMemoryTypeRepository extends TypeRepositoryDecorator
{
    /**
     * @var \WeakMap<TypeStatement, TypeInterface>
     */
    private readonly \WeakMap $types;

    public function __construct(
        TypeRepositoryInterface $delegate,
    ) {
        parent::__construct($delegate);

        $this->types = new \WeakMap();
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
        return $this->types[$statement] ??= parent::getTypeByStatement($statement, $context);
    }
}
