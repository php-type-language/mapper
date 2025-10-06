<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * @template-implements \IteratorAggregate<array-key, PropertyPrototype>
 */
final class PropertyPrototypeSet implements \IteratorAggregate, \Countable
{
    private array $properties = [];

    public function add(PropertyPrototype $property): void
    {
        $this->properties[$property->name] = $property;
    }

    /**
     * @param non-empty-string $name
     */
    public function getOrCreate(string $name): PropertyPrototype
    {
        return $this->properties[$name] ??= new PropertyPrototype($name);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->properties);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->properties);
    }
}
