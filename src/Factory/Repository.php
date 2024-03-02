<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Factory;

/**
 * @template T of object
 *
 * @template-implements \IteratorAggregate<array-key, OptionalTypeFactoryInterface<T>>
 * @template-implements MutableRepositoryInterface<T>
 */
class Repository implements MutableRepositoryInterface, \IteratorAggregate
{
    /**
     * @var list<OptionalTypeFactoryInterface<T>>
     */
    protected array $factories;

    /**
     * @param iterable<array-key, OptionalTypeFactoryInterface<T>> $factories
     */
    public function __construct(iterable $factories)
    {
        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $this->factories = $this->getFactoriesFromIterable($factories);
    }

    /**
     * @param iterable<array-key, OptionalTypeFactoryInterface<T>> $factories
     *
     * @return list<OptionalTypeFactoryInterface<T>>
     */
    private function getFactoriesFromIterable(iterable $factories): array
    {
        if ($factories instanceof \Traversable) {
            return \iterator_to_array($factories, false);
        }

        return \array_values($factories);
    }

    public function add(OptionalTypeFactoryInterface $factory, bool $append = true): void
    {
        if ($append) {
            $this->factories[] = $factory;
        } else {
            $this->factories = [$factory, ...$this->factories];
        }
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->factories);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return \count($this->factories);
    }
}
