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

    public function getByType(#[Language('PHP')] string $type, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getByType($type, $context);
    }

    public function getByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getByValue($value, $context);
    }

    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        // @phpstan-ignore-next-line : PHPStan bug (array assign over readonly)
        return $this->types[$statement]
            ??= $this->delegate->getByStatement($statement, $context);
    }
}
