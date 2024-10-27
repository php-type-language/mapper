<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class InMemoryTypeRepository implements TypeRepositoryInterface
{
    /**
     * @var \WeakMap<TypeStatement, TypeInterface>
     */
    private readonly \WeakMap $types;

    public function __construct(
        private readonly TypeRepositoryInterface $delegate,
    ) {
        $this->types = new \WeakMap();
    }

    public function getTypeByDefinition(#[Language('PHP')] string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getTypeByDefinition($definition, $context);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getTypeByValue($value, $context);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
        return $this->types[$statement]
            ??= $this->delegate->getTypeByStatement($statement, $context);
    }
}
