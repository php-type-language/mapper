<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use Psr\Cache\CacheItemPoolInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Runtime\Parser\TypeParserInterface;
use TypeLang\Mapper\Runtime\Repository\TypeRepositoryInterface;

final class Psr6CacheProvider extends CacheProvider
{
    public function __construct(
        private readonly CacheItemPoolInterface $psr6,
        string $prefix = self::DEFAULT_CACHE_PREFIX,
        \DateInterval|int|null $ttl = self::DEFAULT_CACHE_TTL,
        ReaderInterface|ProviderInterface $delegate = new NullProvider(),
    ) {
        parent::__construct($prefix, $ttl, $delegate);
    }

    public function getClassMetadata(
        \ReflectionClass $class,
        TypeRepositoryInterface $types,
        TypeParserInterface $parser,
    ): ClassMetadata {
        $item = $this->psr6->getItem($this->getKey($class));

        $result = $item->get();

        if ($result instanceof ClassMetadata) {
            return $result;
        }

        $result = parent::getClassMetadata($class, $types, $parser);

        $item->expiresAfter($this->ttl);
        $item->set($result);

        $this->psr6->saveDeferred($item);

        return $result;
    }
}
