<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Context;

use TypeLang\Mapper\Context\Path\EntryInterface;

/**
 * @template-implements \IteratorAggregate<array-key, EntryInterface>
 */
class Path implements PathInterface, \IteratorAggregate
{
    public function __construct(
        /**
         * @var list<EntryInterface>
         */
        private array $entries = [],
    ) {}

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

    public function contains(mixed $value): bool
    {
        foreach ($this->entries as $entry) {
            if ($entry->match($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<class-string<EntryInterface>> $classes
     */
    private function match(EntryInterface $entry, array $classes): bool
    {
        foreach ($classes as $class) {
            if ($entry instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of EntryInterface
     *
     * @param list<class-string<T>> $classes
     *
     * @return list<T>
     */
    public function only(array $classes): array
    {
        $result = [];

        foreach ($this->entries as $entry) {
            if ($this->match($entry, $classes)) {
                $result[] = $entry;
            }
        }

        /** @var list<T> */
        return $result;
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
