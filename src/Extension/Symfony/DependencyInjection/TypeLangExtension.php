<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\Symfony\DependencyInjection;

use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Parser as YamlParser;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\DenormalizerInterface;
use TypeLang\Mapper\Kernel\Extractor\Factory\DefaultTypeExtractorFactory;
use TypeLang\Mapper\Kernel\Extractor\Factory\TypeExtractorFactoryInterface;
use TypeLang\Mapper\Kernel\Instantiator\ClassInstantiatorInterface;
use TypeLang\Mapper\Kernel\Instantiator\DoctrineClassInstantiator;
use TypeLang\Mapper\Kernel\Instantiator\ReflectionClassInstantiator;
use TypeLang\Mapper\Kernel\Parser\Factory\DefaultTypeParserFactory;
use TypeLang\Mapper\Kernel\Parser\Factory\TypeParserFactoryInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\FastPropertyAccessor;
use TypeLang\Mapper\Kernel\PropertyAccessor\PropertyAccessorInterface;
use TypeLang\Mapper\Kernel\PropertyAccessor\ReflectionPropertyAccessor;
use TypeLang\Mapper\Kernel\Repository\Factory\DefaultTypeRepositoryFactory;
use TypeLang\Mapper\Kernel\Repository\Factory\TypeRepositoryFactoryInterface;
use TypeLang\Mapper\Kernel\Tracing\SymfonyStopwatchTracer;
use TypeLang\Mapper\Kernel\Tracing\TracerInterface;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\Provider\InMemoryProvider;
use TypeLang\Mapper\Mapping\Provider\MetadataBuilder;
use TypeLang\Mapper\Mapping\Provider\ProviderInterface;
use TypeLang\Mapper\Mapping\Provider\Psr16CacheProvider;
use TypeLang\Mapper\Mapping\Provider\Psr6CacheProvider;
use TypeLang\Mapper\Mapping\Reader\AttributeReader;
use TypeLang\Mapper\Mapping\Reader\NullReader;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader;
use TypeLang\Mapper\Mapping\Reader\ReaderInterface;
use TypeLang\Mapper\Mapping\Reader\ReflectionReader;
use TypeLang\Mapper\Mapping\Reader\YamlConfigReader;
use TypeLang\Mapper\Mapping\Reference\Reader\NativeReferencesReader;
use TypeLang\Mapper\Mapping\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\NormalizerInterface;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\PHPDoc\Parser as PhpDocParser;
use TypeLang\PHPDoc\Standard\ParamTagFactory;
use TypeLang\Printer\PrettyPrinter;
use TypeLang\Printer\PrinterInterface;

/**
 * @phpstan-type TypeLangConfigType array{
 *     object_as_array: bool|null,
 *     strict_types: bool|null,
 *     types: array<string>,
 *     coercions: array<string, string>,
 *     logger: array{
 *         enabled: bool|null,
 *         service: string,
 *         log_type_extraction: bool,
 *         log_type_parse: bool,
 *         log_type_fetch: bool,
 *         log_type_cast: bool,
 *         log_type_match: bool,
 *         ...
 *     },
 *     tracing: array{
 *         enabled: bool|null,
 *         service: string,
 *         printer: string,
 *         trace_type_extraction: bool,
 *         trace_type_parse: bool,
 *         trace_type_fetch: bool,
 *         trace_type_cast: bool,
 *         trace_type_match: bool,
 *         ...
 *     },
 *     memoization: array{
 *         parser: array{
 *             enabled: bool|null,
 *             min_types: int<0, max>,
 *             max_types: int<1, max>,
 *             ...
 *         },
 *         types: array{
 *             enabled: bool|null,
 *             ...
 *         },
 *         meta: array{
 *             enabled: bool|null,
 *             ...
 *         },
 *         ...
 *     },
 *     cache: array{
 *         enabled: bool|null,
 *         service: string,
 *         driver: "psr6"|"psr16",
 *         prefix: string,
 *         ttl: int|null,
 *         ...
 *     },
 *     meta: array{
 *         attributes: array{
 *             enabled: bool|null,
 *             ...
 *         },
 *         phpdoc: array{
 *             enabled: bool|null,
 *             param_tag_name: string,
 *             var_tag_name: string,
 *             return_tag_name: string,
 *             ...
 *         },
 *         yaml: array{
 *             enabled: bool|null,
 *             directories: list<string>,
 *             extensions: list<string>,
 *             ...
 *         },
 *         ...
 *     },
 *     ...
 * }
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Extension\Symfony
 */
final class TypeLangExtension extends Extension
{
    /**
     * @param array<array-key, mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): TypeLangConfiguration
    {
        return new TypeLangConfiguration();
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var TypeLangConfigType $configs */
        $configs = $this->processConfiguration(new TypeLangConfiguration(), $configs);

        $this->registerTypePrinter($container);
        $this->registerReferencesReader($container);
        $this->registerClassInstantiator($container);
        $this->registerPropertyAccessor($container);
        $this->registerTracer($configs, $container);
        $this->registerTypeExtractor($configs, $container);
        $this->registerTypeParser($configs, $container);
        $this->registerTypeRepository($configs, $container);
        $this->registerConfiguration($configs, $container);
        $this->registerMetadataReaders($configs, $container);
        $this->registerMetadataProviders($configs, $container);
        $this->registerPlatform($configs, $container);

        $this->registerMapper($container);
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerMetadataProviders(array $configs, ContainerBuilder $container): void
    {
        $container->register(ProviderInterface::class, MetadataBuilder::class)
            ->setArgument('$reader', new Reference(ReaderInterface::class))
            ->setArgument('$expression', new Reference(ExpressionLanguage::class, ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->setArgument('$clock', new Reference(ClockInterface::class, ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->setArgument('$references', new Reference(ReferencesReaderInterface::class))
        ;

        if ($this->isCacheEnabled($configs, $container)) {
            (match ($configs['cache']['driver']) {
                'psr6' => $container->register('type_lang.meta.cache', Psr6CacheProvider::class)
                    ->setArgument('$psr6', new Reference($configs['cache']['service'])),
                'psr16' => $container->register('type_lang.meta.cache', Psr16CacheProvider::class)
                    ->setArgument('$psr16', new Reference($configs['cache']['service'])),
            })
                ->setDecoratedService(ProviderInterface::class)
                ->setArgument('$prefix', $configs['cache']['prefix'])
                ->setArgument('$ttl', $configs['cache']['ttl'])
                ->setArgument('$delegate', new Reference('.inner'))
            ;
        }

        if ($configs['memoization']['meta']['enabled'] ?? true) {
            $container->register('type_lang.meta.memoize', InMemoryProvider::class)
                ->setDecoratedService(ProviderInterface::class)
                ->setArgument('$delegate', new Reference('.inner'))
            ;
        }
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerMetadataReaders(array $configs, ContainerBuilder $container): void
    {
        $container->register(ReaderInterface::class, NullReader::class);

        $container->register('type_lang.meta.reflection', ReflectionReader::class)
            ->setDecoratedService(ReaderInterface::class)
            ->setArgument('$delegate', new Reference('.inner'));

        $isMetaAttributesEnabled = $configs['meta']['attributes']['enabled'] ?? true;

        if ($isMetaAttributesEnabled) {
            $container->register('type_lang.meta.attributes', AttributeReader::class)
                ->setDecoratedService(ReaderInterface::class)
                ->setArgument('$delegate', new Reference('.inner'));
        }

        $isMetaPhpDocEnabled = $configs['meta']['phpdoc']['enabled']
            ?? (\class_exists(PhpDocParser::class) && \class_exists(ParamTagFactory::class));

        if ($isMetaPhpDocEnabled) {
            $container->register('type_lang.meta.phpdoc', PhpDocReader::class)
                ->setDecoratedService(ReaderInterface::class)
                ->setArgument('$paramTagName', $configs['meta']['phpdoc']['param_tag_name'])
                ->setArgument('$varTagName', $configs['meta']['phpdoc']['var_tag_name'])
                ->setArgument('$returnTagName', $configs['meta']['phpdoc']['return_tag_name'])
                ->setArgument('$delegate', new Reference('.inner'))
            ;
        }

        $isMetaYamlEnabled = $configs['meta']['yaml']['enabled']
            ?? \class_exists(YamlParser::class);

        if ($isMetaYamlEnabled) {
            $container->register('type_lang.meta.yaml_files', YamlConfigReader::class)
                ->setDecoratedService(ReaderInterface::class)
                ->setArgument('$directories', $configs['meta']['yaml']['directories'])
                ->setArgument('$extensions', $configs['meta']['yaml']['extensions'])
                ->setArgument('$delegate', new Reference('.inner'));
        }
    }

    private function registerMapper(ContainerBuilder $container): void
    {
        $container->register(Mapper::class, Mapper::class)
            ->setArgument('$platform', new Reference(PlatformInterface::class))
            ->setArgument('$config', new Reference(Configuration::class))
        ;

        $container->setAlias(DenormalizerInterface::class, Mapper::class);
        $container->setAlias(NormalizerInterface::class, Mapper::class);
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerPlatform(array $configs, ContainerBuilder $container): void
    {
        $types = $coercers = [];

        foreach ($configs['types'] as $type) {
            $types[] = new Reference($type);
        }

        foreach ($configs['coercions'] as $type => $coercer) {
            $coercers[$type] = new Reference($coercer);
        }

        $container->register(PlatformInterface::class, StandardPlatform::class)
            ->setArgument('$meta', new Reference(ProviderInterface::class))
            ->setArgument('$types', $types)
            ->setArgument('$coercers', $coercers)
            ->setArgument('$classInstantiator', new Reference(ClassInstantiatorInterface::class))
            ->setArgument('$propertyAccessor', new Reference(PropertyAccessorInterface::class))
        ;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerConfiguration(array $configs, ContainerBuilder $container): void
    {
        $container->register(Configuration::class, Configuration::class)
            ->setArgument('$objectAsArray', $configs['object_as_array'])
            ->setArgument('$strictTypes', $configs['strict_types'])
            ->setArgument('$logger', $this->getLoggerReference($configs, $container))
            ->setArgument('$tracer', $this->getTracerReference($configs, $container))
            ->setArgument('$typeExtractorFactory', new Reference(TypeExtractorFactoryInterface::class))
            ->setArgument('$typeParserFactory', new Reference(TypeParserFactoryInterface::class))
            ->setArgument('$typeRepositoryFactory', new Reference(TypeRepositoryFactoryInterface::class))
        ;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function isLoggerEnabled(array $configs, ContainerBuilder $container): bool
    {
        $default = false;

        if ($container->getParameter('kernel.debug') === true) {
            $default = true;
        }

        return $configs['logger']['enabled'] ?? $default;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function isTracingEnabled(array $configs, ContainerBuilder $container): bool
    {
        $default = false;

        if ($container->getParameter('kernel.debug') === true) {
            $default = true;
        }

        return $configs['tracing']['enabled'] ?? $default;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function isCacheEnabled(array $configs, ContainerBuilder $container): bool
    {
        $default = true;

        if ($container->getParameter('kernel.debug') === true) {
            $default = false;
        }

        return $configs['cache']['enabled'] ?? $default;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function getLoggerReference(array $configs, ContainerBuilder $container): ?Reference
    {
        if ($this->isLoggerEnabled($configs, $container)) {
            return new Reference($configs['logger']['service'], ContainerInterface::NULL_ON_INVALID_REFERENCE);
        }

        return null;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function getTracerReference(array $configs, ContainerBuilder $container): ?Reference
    {
        if ($this->isTracingEnabled($configs, $container)) {
            return new Reference($configs['tracing']['service'], ContainerInterface::NULL_ON_INVALID_REFERENCE);
        }

        return null;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerTracer(array $configs, ContainerBuilder $container): void
    {
        if (!$this->isTracingEnabled($configs, $container)) {
            return;
        }

        $container->register(TracerInterface::class, SymfonyStopwatchTracer::class)
            ->setArgument('$stopwatch', new Reference('debug.stopwatch', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
    }

    private function registerTypePrinter(ContainerBuilder $container): void
    {
        $container->register(PrinterInterface::class, PrettyPrinter::class)
            ->setArgument('$wrapUnionType', false)
            ->setArgument('$multilineShape', \PHP_INT_MAX);
    }

    private function registerReferencesReader(ContainerBuilder $container): void
    {
        $container->register(ReferencesReaderInterface::class, NativeReferencesReader::class);
    }

    private function registerClassInstantiator(ContainerBuilder $container): void
    {
        $instance = ReflectionClassInstantiator::class;

        if (DoctrineClassInstantiator::isSupported()) {
            $instance = DoctrineClassInstantiator::class;
        }

        $container->register(ClassInstantiatorInterface::class, $instance)
            ->setAutowired(true);
    }

    private function registerPropertyAccessor(ContainerBuilder $container): void
    {
        $instance = FastPropertyAccessor::class;

        if (\PHP_VERSION_ID >= 80400) {
            $instance = ReflectionPropertyAccessor::class;
        }

        $container->register(PropertyAccessorInterface::class, $instance)
            ->setAutowired(true);
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerTypeRepository(array $configs, ContainerBuilder $container): void
    {
        $factory = $container->register(TypeRepositoryFactoryInterface::class, DefaultTypeRepositoryFactory::class);

        if ($this->isLoggerEnabled($configs, $container)) {
            $factory
                ->setArgument('$enableLogging', $configs['logger']['log_type_fetch'])
                ->setArgument('$enableTypeMatchLogging', $configs['logger']['log_type_match'])
                ->setArgument('$enableTypeCastLogging', $configs['logger']['log_type_cast']);
        }

        if ($this->isTracingEnabled($configs, $container)) {
            $factory
                ->setArgument('$enableTracing', $configs['tracing']['trace_type_fetch'])
                ->setArgument('$enableTypeMatchTracing', $configs['tracing']['trace_type_match'])
                ->setArgument('$enableTypeCastTracing', $configs['tracing']['trace_type_cast']);
        }

        $factory
            ->setArgument('$enableMemoization', $configs['memoization']['types']['enabled'] ?? true)
            ->setArgument('$typeTracingPrinter', new Reference($configs['tracing']['printer']))
        ;
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerTypeExtractor(array $configs, ContainerBuilder $container): void
    {
        $factory = $container->register(TypeExtractorFactoryInterface::class, DefaultTypeExtractorFactory::class);

        if ($this->isLoggerEnabled($configs, $container)) {
            $factory->setArgument('$enableLogging', $configs['logger']['log_type_extraction']);
        }

        if ($this->isTracingEnabled($configs, $container)) {
            $factory->setArgument('$enableTracing', $configs['tracing']['trace_type_extraction']);
        }
    }

    /**
     * @param TypeLangConfigType $configs
     */
    private function registerTypeParser(array $configs, ContainerBuilder $container): void
    {
        $factory = $container->register(TypeParserFactoryInterface::class, DefaultTypeParserFactory::class);

        if ($this->isLoggerEnabled($configs, $container)) {
            $factory->setArgument('$enableLogging', $configs['logger']['log_type_parse']);
        }

        if ($this->isTracingEnabled($configs, $container)) {
            $factory->setArgument('$enableTracing', $configs['tracing']['trace_type_parse']);
        }

        $factory
            ->setArgument('$enableMemoization', $configs['memoization']['parser']['enabled'] ?? true)
            ->setArgument('$maxTypesInMemory', $configs['memoization']['parser']['max_types'])
            ->setArgument('$minTypesInMemory', $configs['memoization']['parser']['min_types'])
        ;
    }
}
