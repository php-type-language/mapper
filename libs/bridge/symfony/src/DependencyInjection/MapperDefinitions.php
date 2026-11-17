<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TypeLang\Mapper\Bridge\Symfony\Profiler\MapperDataCollector;
use TypeLang\Mapper\Bridge\Symfony\Profiler\TraceableMapper;
use TypeLang\Mapper\Bridge\Symfony\Profiler\TraceableMetadataProvider;
use TypeLang\Mapper\Bridge\Symfony\Profiler\TypeBuildRecorder;
use TypeLang\Mapper\Context\Coercers;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\DenormalizerInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\MapperInterface;
use TypeLang\Mapper\Mapping\MetadataProviderFactory;
use TypeLang\Mapper\Mapping\Provider\CacheMetadataProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Mapping\Reader\FileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\JsonFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\NeonFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\PhpFileMetadataReader;
use TypeLang\Mapper\Mapping\Reader\YamlFileMetadataReader;
use TypeLang\Mapper\Naming\CamelCaseNamingStrategy;
use TypeLang\Mapper\Naming\KebabCaseNamingStrategy;
use TypeLang\Mapper\Naming\PascalCaseNamingStrategy;
use TypeLang\Mapper\Naming\ScreamingSnakeCaseNamingStrategy;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;
use TypeLang\Mapper\NormalizerInterface;
use TypeLang\Parser\TypeParser;

/**
 * Registers the services of the mappers from the processed config. The
 * extensions of a mapper (builders, hooks, strategies, readers) are filled
 * in by {@see MapperExtensionsPass}.
 *
 * @phpstan-type MapperSettingsType array{
 *     strict: bool|null,
 *     object_as_array: bool|null,
 *     max_violations: int<1, max>|null,
 *     reject_extra_fields: bool|null,
 *     naming: non-empty-string|null,
 *     types: list<non-empty-string>,
 *     decorators: list<non-empty-string>,
 *     hooks: list<non-empty-string>,
 *     instantiators: list<non-empty-string>,
 *     accessors: list<non-empty-string>,
 *     coercers: array<non-empty-string, non-empty-string|null>,
 *     metadata: array{
 *         readers: list<non-empty-string>,
 *         paths: list<non-empty-string>,
 *         selector: non-empty-string|null,
 *         cache: array{
 *             enabled: bool|null,
 *             pool: non-empty-string|null,
 *         },
 *     },
 *     ...
 * }
 * @phpstan-type BundleConfigType array{
 *     strict: bool|null,
 *     object_as_array: bool|null,
 *     max_violations: int<1, max>|null,
 *     reject_extra_fields: bool|null,
 *     naming: non-empty-string|null,
 *     types: list<non-empty-string>,
 *     decorators: list<non-empty-string>,
 *     hooks: list<non-empty-string>,
 *     instantiators: list<non-empty-string>,
 *     accessors: list<non-empty-string>,
 *     coercers: array<non-empty-string, non-empty-string|null>,
 *     metadata: array{
 *         readers: list<non-empty-string>,
 *         paths: list<non-empty-string>,
 *         selector: non-empty-string|null,
 *         cache: array{
 *             enabled: bool|null,
 *             pool: non-empty-string|null,
 *         },
 *     },
 *     mappers: array<non-empty-string, MapperSettingsType>,
 *     profiler: array{
 *         enabled: bool|null,
 *     },
 * }
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Bridge\Symfony
 */
final class MapperDefinitions
{
    /**
     * Name of the default mapper, inherited by the named ones
     */
    public const DEFAULT_NAME = 'default';

    public const MAPPER_ID = 'type_lang_mapper.mapper';

    public const PARSER_ID = 'type_lang_mapper.parser';

    public const COLLECTOR_ID = 'type_lang_mapper.data_collector';

    /**
     * Contracts of a mapper service, the aliases of the default mapper and
     * the argument aliases of the named ones
     */
    private const MAPPER_INTERFACES = [
        MapperInterface::class,
        DenormalizerInterface::class,
        NormalizerInterface::class,
    ];

    /**
     * Pool of the metadata cache used in case of the config sets none,
     * declared with FrameworkBundle
     */
    public const DEFAULT_CACHE_POOL = 'cache.type_lang_mapper';

    /**
     * Parameter passing the configured extensions to
     * {@see MapperExtensionsPass}, removed by the pass
     */
    public const EXTENSIONS_PARAMETER = '.type_lang_mapper.extensions';

    /**
     * Parameter passing the configured coercers to
     * {@see MapperExtensionsPass}, removed by the pass
     */
    public const COERCERS_PARAMETER = '.type_lang_mapper.coercers';

    public const COERCER_TAG = 'type_lang_mapper.coercer';

    /**
     * Types of the coercers of a mapper, the arguments of {@see Coercers::createDefault()}
     */
    public const COERCED_TYPES = ['int', 'float', 'string', 'bool', 'array'];

    /**
     * Built-in naming strategies by the name of the style written in that
     * style, as in the {@see \TypeLang\Mapper\Mapping\NamingStrategy}
     * attribute
     */
    private const NAMING_STRATEGIES = [
        'camelCase' => CamelCaseNamingStrategy::class,
        'PascalCase' => PascalCaseNamingStrategy::class,
        'snake_case' => SnakeCaseNamingStrategy::class,
        'SCREAMING_SNAKE_CASE' => ScreamingSnakeCaseNamingStrategy::class,
        'kebab-case' => KebabCaseNamingStrategy::class,
    ];

    /**
     * Readers of the mapping config files by the file extension
     */
    private const FILE_READERS = [
        'yaml' => YamlFileMetadataReader::class,
        'yml' => YamlFileMetadataReader::class,
        'neon' => NeonFileMetadataReader::class,
        'json' => JsonFileMetadataReader::class,
        'php' => PhpFileMetadataReader::class,
    ];

    private const FILE_PATTERN = '/\.(?:ya?ml|neon|json|php)$/i';

    public function __construct(
        private readonly ContainerBuilder $container,
    ) {}

    /**
     * Returns the id of the mapper service.
     *
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public static function getMapperId(string $name): string
    {
        if ($name === self::DEFAULT_NAME) {
            return self::MAPPER_ID;
        }

        return self::MAPPER_ID . '.' . $name;
    }

    /**
     * Returns the id of the service of the mapper taking the extensions of
     * the kind.
     *
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public static function getExtensionTargetId(string $name, ExtensionKind $kind): string
    {
        return match ($kind) {
            ExtensionKind::TypeBuilder,
            ExtensionKind::TypeDecorator,
            ExtensionKind::ClassHook,
            ExtensionKind::InstantiationStrategy,
            ExtensionKind::AccessStrategy => self::getMapperId($name),
            ExtensionKind::MetadataReader => self::getInternalId($name, 'metadata'),
        };
    }

    /**
     * @param array<array-key, mixed> $config processed config of the bundle
     * @throws InvalidConfigurationException in case of a mapping config path
     *         does not exist or its format is not supported
     */
    public function register(array $config): void
    {
        /** @var BundleConfigType $config */
        $this->container->register(self::PARSER_ID, TypeParser::class);

        $defaults = $config;
        $extensions = [];
        $coercers = [];

        $extensions[self::DEFAULT_NAME] = $this->registerMapper(self::DEFAULT_NAME, $defaults);
        $coercers[self::DEFAULT_NAME] = self::getConfiguredCoercers($defaults);

        foreach (self::MAPPER_INTERFACES as $type) {
            $this->container->setAlias($type, self::MAPPER_ID);
        }

        foreach ($config['mappers'] as $name => $settings) {
            $extensions[$name] = $this->registerMapper($name, $settings, $defaults);
            $coercers[$name] = self::getConfiguredCoercers($settings);

            foreach (self::MAPPER_INTERFACES as $type) {
                $this->container->registerAliasForArgument(self::getMapperId($name), $type, $name . '.mapper');
            }
        }

        $this->container->setParameter(self::EXTENSIONS_PARAMETER, $extensions);
        $this->container->setParameter(self::COERCERS_PARAMETER, $coercers);

        if ($config['profiler']['enabled'] ?? $this->isDebug()) {
            $this->registerProfilerServices(\array_keys($extensions));
        }
    }

    /**
     * Returns the id of the type decorator reporting the built types of the
     * mapper to the profiler.
     *
     * @param non-empty-string $name
     * @return non-empty-string
     */
    public static function getTypeRecorderId(string $name): string
    {
        return self::getInternalId($name, 'type_recorder');
    }

    /**
     * Decorates the mappers and their metadata providers to report to the
     * collector of the web profiler.
     *
     * @param list<non-empty-string> $mappers
     */
    private function registerProfilerServices(array $mappers): void
    {
        $this->container->register(self::COLLECTOR_ID, MapperDataCollector::class)
            ->addTag('data_collector', [
                'id' => 'type_lang_mapper',
                'template' => '@TypeLangMapper/Collector/mapper.html.twig',
            ]);

        $collector = new Reference(self::COLLECTOR_ID);

        foreach ($mappers as $name) {
            $this->container->register(self::getInternalId($name, 'traceable'), TraceableMapper::class)
                ->setDecoratedService(self::getMapperId($name))
                ->setArguments([
                    '$delegate' => new Reference('.inner'),
                    '$name' => $name,
                    '$collector' => $collector,
                    '$stopwatch' => new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                ]);

            $this->container->register(self::getInternalId($name, 'traceable_metadata'), TraceableMetadataProvider::class)
                ->setDecoratedService(self::getInternalId($name, 'metadata'))
                ->setArguments([
                    '$delegate' => new Reference('.inner'),
                    '$name' => $name,
                    '$collector' => $collector,
                ]);

            $this->container->register(self::getTypeRecorderId($name), TypeBuildRecorder::class)
                ->setArguments(['$name' => $name, '$collector' => $collector]);
        }
    }

    /**
     * Returns the coercer services given in the settings of a mapper by the
     * type, without the inherited ones.
     *
     * @param MapperSettingsType $settings
     * @return array<non-empty-string, non-empty-string>
     */
    private static function getConfiguredCoercers(array $settings): array
    {
        $result = [];

        foreach (self::COERCED_TYPES as $type) {
            $service = $settings['coercers'][$type] ?? null;

            if ($service !== null) {
                $result[$type] = $service;
            }
        }

        return $result;
    }

    /**
     * Registers the mapper, returns the services of its config by the kind
     * of the extension.
     *
     * @param non-empty-string $name
     * @param MapperSettingsType $settings
     * @param MapperSettingsType|null $defaults settings of the default
     *        mapper inherited by the named one
     * @return array<value-of<ExtensionKind>, list<non-empty-string>>
     * @throws InvalidConfigurationException in case of a mapping config path
     *         does not exist or its format is not supported
     */
    private function registerMapper(string $name, array $settings, ?array $defaults = null): array
    {
        $this->container->register(self::getInternalId($name, 'configuration'), Configuration::class)
            ->setArguments(self::filterNulls([
                '$isStrict' => $settings['strict'] ?? $defaults['strict'] ?? null,
                '$isObjectAsArray' => $settings['object_as_array'] ?? $defaults['object_as_array'] ?? null,
                '$maxViolations' => $settings['max_violations'] ?? $defaults['max_violations'] ?? null,
                '$shouldRejectExtraFields' => $settings['reject_extra_fields']
                    ?? $defaults['reject_extra_fields']
                    ?? null,
            ]));

        $selector = $settings['metadata']['selector'] ?? $defaults['metadata']['selector'] ?? null;

        $this->container->register(self::getInternalId($name, 'metadata'), MetadataProviderInterface::class)
            ->setFactory([MetadataProviderFactory::class, 'create'])
            ->setArguments(self::filterNulls([
                '$readers' => [],
                '$strategy' => $this->createNamingStrategy($settings['naming'] ?? $defaults['naming'] ?? null),
                '$cache' => $this->createCacheReference($settings, $defaults),
                '$prefix' => CacheMetadataProvider::DEFAULT_PREFIX . $name . '_',
                '$parser' => new Reference(self::PARSER_ID),
                '$selector' => $selector === null ? null : new Reference($selector),
            ]));

        $this->container->register(self::getMapperId($name), Mapper::class)
            ->setArguments([
                '$config' => new Reference(self::getInternalId($name, 'configuration')),
                '$metadata' => new Reference(self::getInternalId($name, 'metadata')),
                '$builders' => [],
                '$decorators' => [],
                '$parser' => new Reference(self::PARSER_ID),
                '$accessors' => [],
                '$instantiators' => [],
                '$hooks' => [],
                '$coercers' => [],
            ]);

        $extensions = [];

        foreach (ExtensionKind::cases() as $kind) {
            $extensions[$kind->value] = $kind->getConfiguredServices($settings);
        }

        $extensions[ExtensionKind::MetadataReader->value] = [
            ...$extensions[ExtensionKind::MetadataReader->value],
            ...$this->registerFileReaders($settings['metadata']['paths']),
        ];

        return $extensions;
    }

    /**
     * Registers a reader per mapping config file, returns their ids in the
     * order of the paths, the files of a directory by the name.
     *
     * @param list<non-empty-string> $paths
     * @return list<non-empty-string>
     * @throws InvalidConfigurationException in case of a path does not exist
     *         or its format is not supported
     */
    private function registerFileReaders(array $paths): array
    {
        $result = [];

        foreach ($paths as $path) {
            foreach ($this->findConfigFiles($path) as $file) {
                $id = '.type_lang_mapper.file_reader.' . \hash('xxh128', $file);

                if (!$this->container->hasDefinition($id)) {
                    $this->container->register($id, self::getFileReaderClass($file))
                        ->setArguments([$file, new Reference(self::PARSER_ID)]);
                }

                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * @param non-empty-string $path
     * @return list<non-empty-string>
     * @throws InvalidConfigurationException in case of the path does not
     *         exist
     */
    private function findConfigFiles(string $path): array
    {
        /** @var non-empty-string $path */
        $path = $this->container->getParameterBag()->resolveValue($path);

        if (!$this->container->fileExists($path, self::FILE_PATTERN)) {
            throw new InvalidConfigurationException(\sprintf(
                'Mapping config path "%s" does not exist',
                \addcslashes($path, '"'),
            ));
        }

        if (!\is_dir($path)) {
            return [$path];
        }

        $files = \scandir($path);
        $result = [];

        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $file = $path . \DIRECTORY_SEPARATOR . $file;

            if (\is_file($file) && \preg_match(self::FILE_PATTERN, $file) === 1) {
                $result[] = $file;
            }
        }

        return $result;
    }

    /**
     * @param non-empty-string $file
     * @return class-string<FileMetadataReader>
     * @throws InvalidConfigurationException in case of the format of the
     *         file is not supported or its package is not installed
     */
    private static function getFileReaderClass(string $file): string
    {
        $extension = \strtolower(\pathinfo($file, \PATHINFO_EXTENSION));
        $class = self::FILE_READERS[$extension] ?? null;

        if ($class === null) {
            throw new InvalidConfigurationException(\sprintf(
                'Mapping config file "%s" is not supported, supported extensions are %s',
                \addcslashes($file, '"'),
                \implode(', ', \array_keys(self::FILE_READERS)),
            ));
        }

        if (\is_a($class, YamlFileMetadataReader::class, true) && !YamlFileMetadataReader::isAvailable()) {
            throw self::becausePackageIsMissing($file, 'symfony/yaml');
        }

        if (\is_a($class, NeonFileMetadataReader::class, true) && !NeonFileMetadataReader::isAvailable()) {
            throw self::becausePackageIsMissing($file, 'nette/neon');
        }

        if (\is_a($class, JsonFileMetadataReader::class, true) && !JsonFileMetadataReader::isAvailable()) {
            throw self::becausePackageIsMissing($file, 'ext-json');
        }

        return $class;
    }

    /**
     * @param non-empty-string $file
     * @param non-empty-string $package
     */
    private static function becausePackageIsMissing(string $file, string $package): InvalidConfigurationException
    {
        return new InvalidConfigurationException(\sprintf(
            'Mapping config file "%s" requires the "%s" package',
            \addcslashes($file, '"'),
            $package,
        ));
    }

    /**
     * @param non-empty-string|null $name
     */
    private function createNamingStrategy(?string $name): Definition|Reference|null
    {
        if ($name === null) {
            return null;
        }

        $class = self::NAMING_STRATEGIES[$name] ?? null;

        if ($class !== null) {
            return new Definition($class);
        }

        return new Reference($name);
    }

    /**
     * Returns the cache of the metadata, {@see null} in case of it is
     * disabled. The default pool is skipped in case of it is not declared,
     * a pool given in the config or a cache enabled in the config is
     * required.
     *
     * @param MapperSettingsType $settings
     * @param MapperSettingsType|null $defaults
     */
    private function createCacheReference(array $settings, ?array $defaults): ?Reference
    {
        $isEnabled = $settings['metadata']['cache']['enabled']
            ?? $defaults['metadata']['cache']['enabled']
            ?? null;

        $pool = $settings['metadata']['cache']['pool']
            ?? $defaults['metadata']['cache']['pool']
            ?? null;

        if ($isEnabled === false || ($isEnabled === null && $this->isDebug())) {
            return null;
        }

        if ($isEnabled === null && $pool === null) {
            return new Reference(self::DEFAULT_CACHE_POOL, ContainerInterface::NULL_ON_INVALID_REFERENCE);
        }

        return new Reference($pool ?? self::DEFAULT_CACHE_POOL);
    }

    private function isDebug(): bool
    {
        return $this->container->hasParameter('kernel.debug')
            && $this->container->getParameter('kernel.debug') === true;
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $service
     * @return non-empty-string
     */
    private static function getInternalId(string $name, string $service): string
    {
        return '.type_lang_mapper.' . $name . '.' . $service;
    }

    /**
     * @param array<non-empty-string, mixed> $arguments
     * @return array<non-empty-string, mixed>
     */
    private static function filterNulls(array $arguments): array
    {
        return \array_filter($arguments, static fn(mixed $value): bool => $value !== null);
    }
}
