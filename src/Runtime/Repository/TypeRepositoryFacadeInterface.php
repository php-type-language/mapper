<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Exception\Definition\TypeNotFoundException;
use TypeLang\Mapper\Platform\Type\TypeInterface;

interface TypeRepositoryFacadeInterface extends TypeRepositoryInterface
{
    /**
     * @param non-empty-string $definition
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable in case of any internal error occurs
     */
    public function getTypeByDefinition(
        string $definition,
        ?\ReflectionClass $context = null,
    ): TypeInterface;

    /**
     * @param \ReflectionClass<object>|null $context
     *
     * @throws TypeNotFoundException in case of type cannot be loaded
     * @throws \Throwable in case of any internal error occurs
     */
    public function getTypeByValue(
        mixed $value,
        ?\ReflectionClass $context = null,
    ): TypeInterface;
}
