<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata\DiscriminatorMetadata;

use TypeLang\Mapper\Mapping\Metadata\TypePrototype;

/**
 * @template-implements \IteratorAggregate<non-empty-string, TypePrototype>
 */
final class DiscriminatorMapPrototype implements \IteratorAggregate, \Countable
{
    /**
     * @var array<non-empty-string, TypePrototype>
     */
    private array $map = [];

    /**
     * @param non-empty-string $value
     */
    public function add(string $value, TypePrototype $type): void
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
