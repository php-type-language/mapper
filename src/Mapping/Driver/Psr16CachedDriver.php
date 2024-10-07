<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Psr\SimpleCache\CacheInterface;
use TypeLang\Mapper\Mapping\ClassMetadata;
use TypeLang\Mapper\Registry\RegistryInterface;

final class Psr16CachedDriver extends CachedDriver
{
    public function __construct(
        private readonly CacheInterface $cache,
        string $prefix = self::DEFAULT_CACHE_PREFIX,
        \DateInterval|int|null $ttl = self::DEFAULT_TTL,
        DriverInterface $delegate = new ReflectionDriver(),
    ) {
        parent::__construct($prefix, $ttl, $delegate);
    }

    public function getClassMetadata(\ReflectionClass $class, RegistryInterface $types): ClassMetadata
    {
        $index = $this->getKey($class);

        $result = $this->cache->get($index, fn() => $this->delegate->getClassMetadata($class, $types));

        if ($result instanceof \Closure) {
            $result = $result();

            $this->cache->set($index, $result, $this->ttl);
        }

        return $result;
    }
}
