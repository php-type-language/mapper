<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Exception\TypeNotCreatableException;
use TypeLang\Mapper\Exception\TypeNotFoundException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @template-extends \Traversable<array-key, TypeBuilderInterface>
 */
interface RepositoryInterface extends \Traversable, \Countable
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

    /**
     * @return int<0, max>
     */
    public function count(): int;
}
