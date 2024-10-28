<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class InMemoryTypeRepositoryRuntime implements TypeRepositoryRuntimeInterface
{
    /**
     * @var \WeakMap<TypeStatement, TypeInterface>
     */
    private readonly \WeakMap $types;

    public function __construct(
        private readonly TypeRepositoryRuntimeInterface $delegate,
    ) {
        $this->types = new \WeakMap();
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
        return $this->types[$statement]
            ??= $this->delegate->getTypeByStatement($statement, $context);
    }
}
