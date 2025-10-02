<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info\ClassInfo\PropertyInfo;

/**
 * @template-implements \IteratorAggregate<array-key, SkipConditionInfo>
 */
final class SkipConditionsInfoList implements \IteratorAggregate, \Countable
{
    /**
     * @var list<SkipConditionInfo>
     */
    private array $conditions = [];

    public function add(SkipConditionInfo $condition): void
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
