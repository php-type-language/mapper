<?php

declare(strict_types=1);

namespace Serafim\Mapper\Registry;

use Serafim\Mapper\Exception\TypeNotCreatableException;
use Serafim\Mapper\Exception\TypeNotFoundException;
use Serafim\Mapper\PlatformInterface;
use Serafim\Mapper\Type\TypeInterface;
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
     * @throws TypeNotFoundException
     */
    public function get(TypeStatement $type): TypeInterface;
}
