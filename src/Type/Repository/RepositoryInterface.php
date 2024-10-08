<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends \Traversable<array-key, TypeBuilderInterface>
 */
interface RepositoryInterface extends \Traversable, \Countable
{
    /**
     * @param non-empty-string $type
     * @param \ReflectionClass<object>|null $class
     *
     * @throws TypeNotFoundException
     * @throws TypeNotCreatableException
     */
    public function getByType(string $type, ?\ReflectionClass $class = null): TypeInterface;

    /**
     * @param \ReflectionClass<object>|null $class
     *
     * @throws TypeNotFoundException
     * @throws TypeNotCreatableException
     */
    public function getByValue(mixed $value, ?\ReflectionClass $class = null): TypeInterface;

    /**
     * @param \ReflectionClass<object>|null $class
     *
     * @throws TypeNotFoundException
     * @throws TypeNotCreatableException
     */
    public function getByStatement(TypeStatement $statement, ?\ReflectionClass $class = null): TypeInterface;

    /**
     * @return int<0, max>
     */
    public function count(): int;
}
