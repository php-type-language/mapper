<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info\ClassInfo\DiscriminatorInfo;

use TypeLang\Mapper\Mapping\Info\TypeInfo;

/**
 * @template-implements \IteratorAggregate<non-empty-string, TypeInfo>
 */
final class DiscriminatorMapInfo implements \IteratorAggregate, \Countable
{
    /**
     * @var array<non-empty-string, TypeInfo>
     */
    private array $map = [];

    /**
     * @param non-empty-string $value
     */
    public function add(string $value, TypeInfo $type): void
    {
        $this->map[$value] = $type;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->map);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->map);
    }
}
