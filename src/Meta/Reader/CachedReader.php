<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta\Reader;

abstract class CachedReader extends Reader
{
    /**
     * @var non-empty-string
     */
    protected const DEFAULT_CACHE_PREFIX = 'mapper_';

    protected const DEFAULT_TTL = null;

    public function __construct(
        protected readonly string $prefix = self::DEFAULT_CACHE_PREFIX,
        protected readonly \DateInterval|int|null $ttl = self::DEFAULT_TTL,
        protected readonly ReaderInterface $delegate = new ReflectionReader(),
    ) {}

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    protected function getKey(\ReflectionClass $class): string
    {
        return $this->prefix
            . self::getKeyValue($class)
            . self::getKeySuffix($class);
    }

    /**
     * @param \ReflectionClass<object> $class
     *
     * @return non-empty-string
     */
    protected static function getKeyValue(\ReflectionClass $class): string
    {
        return \str_replace("\\\0", '_', $class->getName());
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
