<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use TypeLang\Mapper\Mapping\Provider\CacheMetadataProvider;
use TypeLang\Mapper\Mapping\Provider\InMemoryMetadataProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Mapping\Provider\Psr16MetadataProvider;
use TypeLang\Mapper\Mapping\Provider\Psr6MetadataProvider;
use TypeLang\Mapper\Mapping\Provider\ValidatingMetadataProvider;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Reader\Reflection\PublicPropertySelector;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Naming\NamingStrategyInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\PhpDoc\DocBlockParser;

/**
 * Creates the default provider stack.
 */
final class MetadataProviderFactory
{
    /**
     * Creates the stack from the outer provider to the inner one: in-memory
     * cache, PSR cache (if any), final checks and the chain of the naming
     * strategies, the given readers, attributes, phpdoc (if the
     * `type-lang/phpdoc` package is installed), reflection.
     *
     * @param list<MetadataReaderInterface> $readers readers above the
     *        default ones, from the highest priority to the lowest one
     * @param NamingStrategyInterface|null $strategy strategy of the classes
     *        without their own, the names are kept in case of {@see null}
     * @param string $prefix prefix of the keys of the cache
     * @param PropertySelectorInterface $selector rule choosing the mapped
     *        properties of a class
     */
    public static function create(
        array $readers = [],
        ?NamingStrategyInterface $strategy = null,
        CacheItemPoolInterface|CacheInterface|null $cache = null,
        string $prefix = CacheMetadataProvider::DEFAULT_PREFIX,
        TypeParser $parser = new TypeParser(),
        PropertySelectorInterface $selector = new PublicPropertySelector(),
    ): MetadataProviderInterface {
        $provider = self::createValidatingProvider([
            new NamingStrategyMetadataReader($strategy),
            ...$readers,
            ...self::getDefaultReaders($parser->forType(), $selector),
        ]);

        return new InMemoryMetadataProvider(
            self::wrapWithCache($provider, $cache, $prefix),
        );
    }

    /**
     * @param list<MetadataReaderInterface> $readers
     */
    private static function createValidatingProvider(array $readers): MetadataProviderInterface
    {
        return new ValidatingMetadataProvider(
            new ChainMetadataReader($readers),
        );
    }

    /**
     * @return non-empty-list<MetadataReaderInterface>
     */
    private static function getDefaultReaders(TypeParser $parser, PropertySelectorInterface $selector): array
    {
        $result = [new AttributeMetadataReader($parser)];

        if (PhpDocMetadataReader::isAvailable()) {
            // parses `type-lang-*` prefixes
            $result[] = new PhpDocMetadataReader(new DocBlockParser(
                platforms: [MapperPlatform::createWithSpecialTagNames()],
                typeParser: $parser,
            ));

            // parses general definitions
            $result[] = new PhpDocMetadataReader(new DocBlockParser(
                platforms: [MapperPlatform::createWithDefaultTagNames()],
                typeParser: $parser,
            ));
        }

        $result[] = new ReflectionMetadataReader($selector);

        return $result;
    }

    private static function wrapWithCache(
        MetadataProviderInterface $provider,
        CacheItemPoolInterface|CacheInterface|null $cache,
        string $prefix,
    ): MetadataProviderInterface {
        if ($cache instanceof CacheItemPoolInterface) {
            return new Psr6MetadataProvider($provider, $cache, $prefix);
        }

        if ($cache instanceof CacheInterface) {
            return new Psr16MetadataProvider($provider, $cache, $prefix);
        }

        return $provider;
    }
}
