<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Registry;

use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\PlatformInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

interface RegistryInterface
{
    /**
     * Returns platform of the current registry set.
     */
    public function getPlatform(): PlatformInterface;

    /**
     * @param non-empty-string $type
     *
     * @throws TypeNotCreatableException
     */
    public function parse(string $type): TypeStatement;

    /**
     * @return TypeInterface<mixed, mixed>
     * @throws TypeNotFoundException
     */
    public function get(TypeStatement $type): TypeInterface;
}
