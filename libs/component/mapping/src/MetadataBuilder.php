<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Provider\CacheMetadataProvider;
use TypeLang\Mapper\Mapping\Provider\InMemoryMetadataProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Mapping\Provider\Psr16MetadataProvider;
use TypeLang\Mapper\Mapping\Provider\Psr6MetadataProvider;
use TypeLang\Mapper\Mapping\Provider\ReaderMetadataProvider;
use TypeLang\Mapper\Mapping\Reader\AttributeMetadataReader;
use TypeLang\Mapper\Mapping\Reader\ChainMetadataReader;
use TypeLang\Mapper\Mapping\Reader\JsonFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Mapping\Reader\NeonFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocMetadataReader\MapperPlatform;
use TypeLang\Mapper\Mapping\Reader\PhpFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\Reflection\PropertySelectorInterface;
use TypeLang\Mapper\Mapping\Reader\Reflection\PublicPropertySelector;
use TypeLang\Mapper\Mapping\Reader\ReflectionMetadataReader;
use TypeLang\Mapper\Mapping\Reader\YamlFileMetadataReader;
use TypeLang\Mapper\Naming\NamingStrategyInterface;
use TypeLang\Parser\TypeParser;
use TypeLang\PhpDoc\DocBlockParser;

/**
 * Builds the metadata provider from the readers and the caches. Each
 * `with*()` method returns a new builder.
 *
 * A reader of a higher priority overrides the values of a lower one, of
 * readers with an equal priority the later added one is higher. The
 * reflection is the lowest reader. The provider keeps the read metadata in
 * memory, then in the caches from the first added one to the last one, and
 * checks the metadata of the readers once.
 *
 * ```
 * $metadata = MetadataBuilder::createDefault()
 *     ->withYamlReader(__DIR__ . '/mapping.yaml', 400)
 *     ->withPsr16Cache($redis, 'hot_')
 *     ->withPsr6Cache($files, 'cold_')
 *     ->build();
 * ```
 */
final class MetadataBuilder
{
    /**
     * Priority of the docblock reader of {@see createDefault()}
     */
    public const PHPDOC_PRIORITY = 100;

    /**
     * Priority of the docblock reader of the `@type-lang-*` tags of
     * {@see createDefault()}
     */
    public const SPECIAL_PHPDOC_PRIORITY = 200;

    /**
     * Priority of the attribute reader of {@see createDefault()}
     */
    public const ATTRIBUTE_PRIORITY = 300;

    private readonly TypeParser $parser;

    /**
     * @var list<array{reader: MetadataReaderInterface, priority: int}>
     */
    private array $readers = [];

    private MetadataReaderInterface $reflection;

    private ?NamingStrategyInterface $strategy = null;

    /**
     * @var list<\Closure(MetadataProviderInterface): MetadataProviderInterface>
     */
    private array $caches = [];

    /**
     * @param TypeParser $parser parser of the types of the readers created
     *        by the builder
     * @param ExpressionLanguage|null $expressions parser of the skip
     *        expressions of the attribute reader, created on its first
     *        expression in case of {@see null}
     */
    public function __construct(
        TypeParser $parser = new TypeParser(),
        private readonly ?ExpressionLanguage $expressions = null,
    ) {
        $this->parser = $parser->forType();
        $this->reflection = new ReflectionMetadataReader();
    }

    /**
     * Creates the builder of the default readers: attributes
     * ({@see ATTRIBUTE_PRIORITY}), docblocks in case of the `type-lang/phpdoc`
     * package is installed ({@see SPECIAL_PHPDOC_PRIORITY} and
     * {@see PHPDOC_PRIORITY}) and reflection.
     */
    public static function createDefault(
        TypeParser $parser = new TypeParser(),
        ?ExpressionLanguage $expressions = null,
    ): self {
        $result = (new self($parser, $expressions))
            ->withAttributeReader(self::ATTRIBUTE_PRIORITY);

        if (!PhpDocMetadataReader::isAvailable()) {
            return $result;
        }

        return $result
            ->withPhpDocReader(MapperPlatform::createWithDefaultTagNames(), self::PHPDOC_PRIORITY)
            ->withPhpDocReader(MapperPlatform::createWithSpecialTagNames(), self::SPECIAL_PHPDOC_PRIORITY);
    }

    /**
     * Returns the builder with the strategy of the names of the classes
     * without their own, the names are kept in case of {@see null}.
     */
    public function withNamingStrategy(?NamingStrategyInterface $strategy): self
    {
        $self = clone $this;
        $self->strategy = $strategy;

        return $self;
    }

    /**
     * Returns the builder with the reflection reader of the properties of
     * the selector.
     */
    public function withReflectionReader(
        PropertySelectorInterface $selector = new PublicPropertySelector(),
    ): self {
        $self = clone $this;
        $self->reflection = new ReflectionMetadataReader($selector);

        return $self;
    }

    public function withAttributeReader(int $priority = 0): self
    {
        return $this->withAddedReader(
            new AttributeMetadataReader($this->parser, $this->expressions),
            $priority,
        );
    }

    /**
     * @param MapperPlatform|null $tags names of the tags, the default ones
     *        in case of {@see null}
     * @throws MissingDependencyException in case of the `type-lang/phpdoc`
     *         package is not installed
     */
    public function withPhpDocReader(?MapperPlatform $tags = null, int $priority = 0): self
    {
        if (!PhpDocMetadataReader::isAvailable()) {
            throw MissingDependencyException::becausePackageIsNotInstalled('type-lang/phpdoc', 'PHPDoc tags');
        }

        $parser = new DocBlockParser(
            platforms: [$tags ?? MapperPlatform::createWithDefaultTagNames()],
            typeParser: $this->parser,
        );

        return $this->withAddedReader(new PhpDocMetadataReader($parser), $priority);
    }

    /**
     * @param non-empty-string $path
     * @throws MissingDependencyException in case of the `symfony/yaml`
     *         package is not installed
     */
    public function withYamlReader(string $path, int $priority = 0): self
    {
        return $this->withAddedReader(
            new YamlFileMetadataReader($path, $this->parser, $this->expressions),
            $priority,
        );
    }

    /**
     * @param non-empty-string $path
     * @throws MissingDependencyException in case of the `nette/neon`
     *         package is not installed
     */
    public function withNeonReader(string $path, int $priority = 0): self
    {
        return $this->withAddedReader(
            new NeonFileMetadataReader($path, $this->parser, $this->expressions),
            $priority,
        );
    }

    /**
     * @param non-empty-string $path
     * @throws MissingDependencyException in case of `json_decode()` is not
     *         available
     */
    public function withJsonReader(string $path, int $priority = 0): self
    {
        return $this->withAddedReader(
            new JsonFileMetadataReader($path, $this->parser, $this->expressions),
            $priority,
        );
    }

    /**
     * @param non-empty-string $path
     */
    public function withPhpReader(string $path, int $priority = 0): self
    {
        return $this->withAddedReader(
            new PhpFileMetadataReader($path, $this->parser, $this->expressions),
            $priority,
        );
    }

    /**
     * Returns the builder with the given reader, its dependencies are its
     * own ones.
     */
    public function withAddedReader(MetadataReaderInterface $reader, int $priority = 0): self
    {
        $self = clone $this;
        $self->readers[] = ['reader' => $reader, 'priority' => $priority];

        return $self;
    }

    /**
     * Returns the builder with the PSR-6 cache under the caches added
     * before.
     *
     * @param string $prefix prefix of the cache keys, a new prefix discards
     *        the cached metadata
     */
    public function withPsr6Cache(
        CacheItemPoolInterface $cache,
        string $prefix = CacheMetadataProvider::DEFAULT_PREFIX,
    ): self {
        $self = clone $this;
        $self->caches[] = static fn(MetadataProviderInterface $delegate): MetadataProviderInterface
            => new Psr6MetadataProvider($delegate, $cache, $prefix);

        return $self;
    }

    /**
     * Returns the builder with the PSR-16 cache under the caches added
     * before.
     *
     * @param string $prefix prefix of the cache keys, a new prefix discards
     *        the cached metadata
     */
    public function withPsr16Cache(
        CacheInterface $cache,
        string $prefix = CacheMetadataProvider::DEFAULT_PREFIX,
    ): self {
        $self = clone $this;
        $self->caches[] = static fn(MetadataProviderInterface $delegate): MetadataProviderInterface
            => new Psr16MetadataProvider($delegate, $cache, $prefix);

        return $self;
    }

    public function build(): MetadataProviderInterface
    {
        $provider = new ReaderMetadataProvider(new ChainMetadataReader([
            new NamingStrategyMetadataReader($this->strategy),
            ...$this->getSortedReaders(),
            $this->reflection,
        ]));

        foreach (\array_reverse($this->caches) as $cache) {
            $provider = $cache($provider);
        }

        return new InMemoryMetadataProvider($provider);
    }

    /**
     * @return list<MetadataReaderInterface>
     */
    private function getSortedReaders(): array
    {
        $entries = \array_reverse($this->readers);

        \usort($entries, static fn(array $a, array $b): int => $b['priority'] <=> $a['priority']);

        $result = [];

        foreach ($entries as $entry) {
            $result[] = $entry['reader'];
        }

        return $result;
    }
}
