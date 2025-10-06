<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use TypeLang\Mapper\Mapping\Reader\ReaderInterface;

abstract class CacheProvider extends Decorator
{
    /**
     * @var non-empty-string
     */
    protected const DEFAULT_CACHE_PREFIX = 'tlm_';

    protected const DEFAULT_CACHE_TTL = null;

    public function __construct(
        protected readonly string $prefix = self::DEFAULT_CACHE_PREFIX,
        protected readonly \DateInterval|int|null $ttl = self::DEFAULT_CACHE_TTL,
        ReaderInterface|ProviderInterface $delegate = new NullProvider(),
    ) {
        parent::__construct($delegate);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    protected function getKey(\ReflectionClass $class): string
    {
        return $this->prefix
            . self::getKeyName($class)
            . self::getKeySuffix($class);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    protected static function getKeyName(\ReflectionClass $class): string
    {
        return \str_replace(['\\', "\0"], '_', \strtolower($class->name));
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    protected static function getKeySuffix(\ReflectionClass $class): string
    {
        $pathname = $class->getFileName();

        if ($pathname === false) {
            return '';
        }

        return (string) \filemtime($pathname);
    }
}
