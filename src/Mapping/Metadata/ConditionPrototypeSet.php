<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata;

/**
 * @template-implements \IteratorAggregate<array-key, ConditionPrototype>
 */
final class ConditionPrototypeSet implements \IteratorAggregate, \Countable
{
    /**
     * @var list<ConditionPrototype>
     */
    private array $conditions = [];

    public function add(ConditionPrototype $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->conditions);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->conditions);
    }
}
