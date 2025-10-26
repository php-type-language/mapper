<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Provider;

use Psr\SimpleCache\CacheInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

final class Psr16CacheProvider extends CacheProvider
{
    public function __construct(
        private readonly CacheInterface $psr16,
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
        $result = $this->psr16->get($key = $this->getKey($class));

        if (!$result instanceof ClassMetadata) {
            $result = parent::getClassMetadata($class, $types, $parser);

            $this->psr16->set($key, $result, $this->ttl);
        }

        return $result;
    }
}
