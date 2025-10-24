<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context\Path;

use TypeLang\Mapper\Context\Path\Entry\EntryInterface;

/**
 * @template-implements \IteratorAggregate<array-key, EntryInterface>
 */
final class Path implements PathInterface, \IteratorAggregate
{
    public function __construct(
        /**
         * @var list<EntryInterface>
         */
        protected readonly array $entries = [],
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
