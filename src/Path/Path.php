<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Path;

use TypeLang\Mapper\Path\Entry\EntryInterface;

/**
 * @template-implements \IteratorAggregate<array-key, EntryInterface>
 */
class Path implements PathInterface, \IteratorAggregate
{
    public function __construct(
        /**
         * @var list<EntryInterface>
         */
        protected array $entries = [],
    ) {}

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->entries);
    }

    public function toArray(): array
    {
        return $this->entries;
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->entries);
    }
}
