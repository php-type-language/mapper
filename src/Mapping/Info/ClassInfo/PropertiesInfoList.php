<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Info\ClassInfo;

/**
 * @template-implements \IteratorAggregate<array-key, PropertyInfo>
 */
final class PropertiesInfoList implements \IteratorAggregate, \Countable
{
    private array $properties = [];

    public function add(PropertyInfo $property): void
    {
        $this->properties[$property->name] = $property;
    }

    /**
     * @param non-empty-string $name
     */
    public function getOrCreate(string $name): PropertyInfo
    {
        return $this->properties[$name] ??= new PropertyInfo($name);
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
