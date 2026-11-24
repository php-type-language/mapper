<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use TypeLang\Mapper\Mapping\Exception\InvalidCacheKeyException;

/**
 * Stores metadata provided by the delegate in a PSR-6 cache pool. A failure
 * of the pool, including a stored value that cannot be restored, is a miss
 * on reading and is skipped on writing.
 */
final class Psr6MetadataProvider extends CacheMetadataProvider
{
    /**
     * @param string $prefix prefix of the cache keys, a new prefix discards
     *        the cached metadata
     */
    public function __construct(
        MetadataProviderInterface $delegate,
        private readonly CacheItemPoolInterface $pool,
        string $prefix = self::DEFAULT_PREFIX,
    ) {
        parent::__construct($delegate, $prefix);
    }

    protected function remember(string $key, string $class, string $type, \Closure $read): object
    {
        try {
            $item = $this->pool->getItem($key);
            $metadata = $item->get();
        } catch (InvalidArgumentException $e) {
            throw InvalidCacheKeyException::becauseKeyIsRejected($key, $e);
        } catch (\Throwable) {
            $item = null;
            $metadata = null;
        }

        if (self::isMetadataOf($metadata, $class, $type)) {
            return $metadata;
        }

        $metadata = $read();

        $this->store($key, $item, $metadata);

        return $metadata;
    }

    /**
     * Saves the metadata by the item, an item that cannot be read is
     * deleted and created again.
     *
     * @param non-empty-string $key
     */
    private function store(string $key, ?CacheItemInterface $item, object $metadata): void
    {
        try {
            if ($item === null) {
                $this->pool->deleteItem($key);
                $item = $this->pool->getItem($key);
            }

            $this->pool->save($item->set($metadata));
        } catch (\Throwable) {
            // a failure of the pool skips writing
        }
    }
}
