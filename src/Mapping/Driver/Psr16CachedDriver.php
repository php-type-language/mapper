<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use Psr\SimpleCache\CacheInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class Psr16CachedDriver extends CachedDriver
{
    public function __construct(
        private readonly CacheInterface $cache,
        string $prefix = self::DEFAULT_CACHE_PREFIX,
        \DateInterval|int|null $ttl = self::DEFAULT_TTL,
        DriverInterface $delegate = new NullDriver(),
    ) {
        parent::__construct($prefix, $ttl, $delegate);
    }

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        $index = $this->getKey($class);

        $result = $this->cache->get(
            $index,
            fn(): ClassMetadata
            => parent::getClassMetadata($class, $types, $parser)
        );

        if ($result instanceof \Closure) {
            $result = $result();

            $this->cache->set($index, $result, $this->ttl);
        }

        return $result;
    }
}
