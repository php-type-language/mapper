<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Exception\InvalidCacheKeyException;
use TypeLang\Mapper\Mapping\Exception\ReaderException;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\EnumMetadata;

/**
 * Stores metadata provided by the delegate in a PSR cache. The key of the
 * metadata of a class is the prefix, {@see ClassMetadata::FORMAT_VERSION},
 * the kind of the metadata and the hash of the class name, a value other
 * than the metadata of the class is a miss.
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
        $read = fn(): ClassMetadata => $this->delegate->read($class);

        /** @var ClassMetadata<TArg> */
        return $this->remember($this->createKey('c', $class->name), $class->name, ClassMetadata::class, $read);
    }

    /**
     * @template TArg of \UnitEnum
     * @param \ReflectionEnum<TArg> $enum
     * @return EnumMetadata<TArg>
     * @throws InvalidCacheKeyException in case of the cache rejects the key
     * @throws ReaderException in case of the delegate cannot provide the
     *         metadata
     */
    final public function readEnum(\ReflectionEnum $enum): EnumMetadata
    {
        $read = fn(): EnumMetadata => $this->delegate->readEnum($enum);

        /** @var EnumMetadata<TArg> */
        return $this->remember($this->createKey('e', $enum->name), $enum->name, EnumMetadata::class, $read);
    }

    /**
     * Returns the metadata of the class stored by the key, in case of a miss
     * reads it by the callback and stores it. A failure of the cache is a
     * miss on reading and is skipped on writing.
     *
     * @template TArgMetadata of object
     * @param non-empty-string $key valid for PSR-6 and PSR-16 in case of the
     *        prefix is
     * @param class-string $class
     * @param class-string<TArgMetadata> $type
     * @param \Closure(): TArgMetadata $read
     * @return TArgMetadata
     * @throws InvalidCacheKeyException in case of the cache rejects the key
     * @throws ReaderException in case of the callback cannot read the
     *         metadata
     */
    abstract protected function remember(string $key, string $class, string $type, \Closure $read): object;

    /**
     * Returns {@see true} in case of the stored value is the metadata of the
     * given type of the class, any other value is a miss.
     *
     * @template TArgMetadata of object
     * @param class-string<TArgMetadata> $type
     *
     * @phpstan-assert-if-true TArgMetadata $value
     */
    protected static function isMetadataOf(mixed $value, string $class, string $type): bool
    {
        return $value instanceof $type
            && ($value instanceof ClassMetadata || $value instanceof EnumMetadata)
            && $value->name === $class;
    }

    /**
     * @param 'c'|'e' $kind
     * @return non-empty-string
     */
    private function createKey(string $kind, string $class): string
    {
        return $this->prefix . 'v' . ClassMetadata::FORMAT_VERSION . $kind . '_' . \hash('xxh128', $class);
    }
}
