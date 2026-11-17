<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * PSR-6 pool keeping serialized values in an array.
 */
final class ArrayCachePool implements CacheItemPoolInterface
{
    /**
     * @var array<string, string>
     */
    public array $values = [];

    /**
     * Exception thrown by {@see getItem()} and {@see save()}, {@see null}
     * in case of the pool works
     */
    public ?\Throwable $exception = null;

    public function getItem(string $key): CacheItemInterface
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        if (!isset($this->values[$key])) {
            return new ArrayCacheItem($key);
        }

        return new ArrayCacheItem($key, \unserialize($this->values[$key]), true);
    }

    /**
     * @param array<array-key, string> $keys
     * @return array<string, CacheItemInterface>
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        return isset($this->values[$key]);
    }

    public function clear(): bool
    {
        $this->values = [];

        return true;
    }

    public function deleteItem(string $key): bool
    {
        unset($this->values[$key]);

        return true;
    }

    /**
     * @param array<array-key, string> $keys
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->values[$key]);
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        $this->values[$item->getKey()] = \serialize($item->get());

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->save($item);
    }

    public function commit(): bool
    {
        return true;
    }
}
