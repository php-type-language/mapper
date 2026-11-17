<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Stub;

use Psr\SimpleCache\CacheInterface;

/**
 * PSR-16 cache keeping serialized values in an array, TTL is ignored.
 */
final class ArrayCache implements CacheInterface
{
    /**
     * @var array<string, string>
     */
    public array $values = [];

    /**
     * Exception thrown by {@see get()} and {@see set()}, {@see null} in
     * case of the cache works
     */
    public ?\Throwable $exception = null;

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        if (!isset($this->values[$key])) {
            return $default;
        }

        return \unserialize($this->values[$key]);
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        if ($this->exception !== null) {
            throw $this->exception;
        }

        $this->values[$key] = \serialize($value);

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->values[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->values = [];

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @param iterable<mixed, mixed> $values
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            \assert(\is_string($key));

            $this->set($key, $value);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            unset($this->values[$key]);
        }

        return true;
    }

    public function has(string $key): bool
    {
        return isset($this->values[$key]);
    }
}
