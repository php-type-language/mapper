<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context\Path;

use TypeLang\Mapper\Type\Context\Path\Entry\EntryInterface;

class MutablePath extends Path implements ExecutionStackInterface
{
    public function enter(EntryInterface $entry): void
    {
        $this->entries[] = $entry;
    }

    public function leave(): void
    {
        if ($this->entries !== []) {
            \array_pop($this->entries);
        }
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->entries);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->entries);
    }
}
