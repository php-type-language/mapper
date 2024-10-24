<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface TypeRepositoryInterface
{
    /**
     * @param non-empty-string $type
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable in case of any internal error occurs
     */
    public function getByType(string $type, ?\ReflectionClass $context = null): TypeInterface;

    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable in case of any internal error occurs
     */
    public function getByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface;

    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable in case of any internal error occurs
     */
    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface;
}
