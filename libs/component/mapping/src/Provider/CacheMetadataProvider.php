<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Exception\InvalidCacheKeyException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Stores metadata provided by the delegate in a PSR cache. The key of the
 * metadata of a class is the prefix, {@see ClassMetadata::FORMAT_VERSION}
 * and the hash of the class name, a value other than the metadata of the
 * class is a miss.
 */
abstract class CacheMetadataProvider implements MetadataProviderInterface
{
    /**
     * Prefix of the cache keys used in case of the user sets none
     */
    public const DEFAULT_PREFIX = 'tlm_';

    public function __construct(
        private readonly MetadataProviderInterface $delegate,
        /**
         * Prefix of the cache keys, a new prefix discards the cached metadata
         */
        private readonly string $prefix = self::DEFAULT_PREFIX,
    ) {}

    /**
     * @template TArg of object
     * @param \ReflectionClass<TArg> $class
     * @return ClassMetadata<TArg>
     * @throws InvalidCacheKeyException in case of the cache rejects the key
     * @throws ReaderException in case of the delegate cannot provide the
     *         metadata
     */
    final public function read(\ReflectionClass $class): ClassMetadata
    {
        $key = $this->prefix . 'v' . ClassMetadata::FORMAT_VERSION . '_' . \hash('xxh128', $class->name);

        /** @var ClassMetadata<TArg> */
        return $this->remember($key, $class->name, fn(): ClassMetadata => $this->delegate->read($class));
    }

    /**
     * Returns the metadata of the class stored by the key, in case of a miss
     * reads it by the callback and stores it. A failure of the cache is a
     * miss on reading and is skipped on writing.
     *
     * @param non-empty-string $key valid for PSR-6 and PSR-16 in case of the
     *        prefix is
     * @param class-string $class
     * @param \Closure(): ClassMetadata<object> $read
     * @return ClassMetadata<object>
     * @throws InvalidCacheKeyException in case of the cache rejects the key
     * @throws ReaderException in case of the callback cannot read the
     *         metadata
     */
    abstract protected function remember(string $key, string $class, \Closure $read): ClassMetadata;

    /**
     * Returns {@see true} in case of the stored value is the metadata of the
     * class, any other value is a miss.
     *
     * @template TArgClass of object
     * @param class-string<TArgClass> $class
     *
     * @phpstan-assert-if-true ClassMetadata<TArgClass> $value
     */
    protected static function isMetadataOf(mixed $value, string $class): bool
    {
        return $value instanceof ClassMetadata
            && $value->name === $class;
    }
}
