<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Extension\Symfony\DependencyInjection;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use TypeLang\Mapper\Kernel\Extractor\Factory\DefaultTypeExtractorFactory;
use TypeLang\Mapper\Kernel\Parser\Factory\DefaultTypeParserFactory;
use TypeLang\Mapper\Kernel\Repository\Factory\DefaultTypeRepositoryFactory;
use TypeLang\Mapper\Kernel\Tracing\TracerInterface;
use TypeLang\Mapper\Mapping\Reader\PhpDocReader;
use TypeLang\Mapper\Mapping\Reader\YamlConfigReader;
use TypeLang\Printer\PrinterInterface;

final class TypeLangConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $tree = new TreeBuilder('type_lang');

        $root = $tree->getRootNode();

        /** @phpstan-ignore-next-line : Known Symfony's issue */
        $config = $root->children();

        /** @phpstan-ignore-next-line : Known Symfony's issue */
        $config = $this->withLoggerConfiguration($config);
        $config = $this->withTracingConfiguration($config);
        $config = $this->withMemoizationConfiguration($config);
        $config = $this->withCacheConfiguration($config);
        $config = $this->withMetadataConfiguration($config);
        $config = $this->withTypesConfiguration($config);
        $config = $this->withTypeCoercionsConfiguration($config);

        /** @phpstan-ignore-next-line : Known Symfony's issue */
        $config
            ->booleanNode('object_as_array')
                ->defaultNull()
            ->end()
            ->booleanNode('strict_types')
                ->defaultNull()
            ->end()
        ->end();

        return $tree;
    }

    private function withTypesConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('types')
            ->stringPrototype()
                ->cannotBeEmpty()
            ->end()
            ->defaultValue([])
        ->end();
    }

    private function withTypeCoercionsConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('coercions')
            ->stringPrototype()
                ->cannotBeEmpty()
            ->end()
            ->useAttributeAsKey('type')
            ->defaultValue([])
        ->end();
    }

    private function withMetadataConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('meta')
            ->children()
                ->arrayNode('attributes')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultValue(null)
                        ->end()
                    ->end()
                    ->addDefaultsIfNotSet()
                ->end()
                ->arrayNode('phpdoc')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultValue(null)
                        ->end()
                        ->stringNode('param_tag_name')
                            ->defaultValue(PhpDocReader::DEFAULT_PARAM_TAG_NAME)
                            ->cannotBeEmpty()
                        ->end()
                        ->stringNode('var_tag_name')
                            ->defaultValue(PhpDocReader::DEFAULT_VAR_TAG_NAME)
                            ->cannotBeEmpty()
                        ->end()
                        ->stringNode('return_tag_name')
                            ->defaultValue(PhpDocReader::DEFAULT_RETURN_TAG_NAME)
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                    ->addDefaultsIfNotSet()
                ->end()
                ->arrayNode('yaml')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultValue(null)
                        ->end()
                        ->arrayNode('directories')
                            ->stringPrototype()
                                ->cannotBeEmpty()
                            ->end()
                            ->defaultValue(['%kernel.project_dir%/config/mapper'])
                        ->end()
                        ->arrayNode('extensions')
                            ->stringPrototype()
                                ->cannotBeEmpty()
                            ->end()
                            ->defaultValue(YamlConfigReader::DEFAULT_YAML_FILE_EXTENSIONS)
                        ->end()
                    ->end()
                    ->addDefaultsIfNotSet()
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }

    private function withCacheConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('cache')
            ->children()
                ->booleanNode('enabled')
                    ->defaultNull()
                ->end()
                ->stringNode('service')
                    ->defaultValue('cache.app')
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('driver')
                    ->values(['psr6', 'psr16'])
                    ->cannotBeEmpty()
                    ->defaultValue('psr6')
                ->end()
                ->stringNode('prefix')
                    ->defaultValue('tlm_')
                ->end()
                ->integerNode('ttl')
                    ->defaultNull()
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }

    private function withMemoizationConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('memoization')
            ->children()
                    ->arrayNode('parser')
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultNull()
                            ->end()
                            ->integerNode('min_types')
                                ->defaultValue(DefaultTypeParserFactory::DEFAULT_MIN_IN_MEMORY_TYPES)
                                ->min(0)
                            ->end()
                            ->integerNode('max_types')
                                ->defaultValue(DefaultTypeParserFactory::DEFAULT_MAX_IN_MEMORY_TYPES)
                                ->min(1)
                            ->end()
                        ->end()
                        ->addDefaultsIfNotSet()
                    ->end()
                ->end()
                ->children()
                    ->arrayNode('types')
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultNull()
                            ->end()
                        ->end()
                        ->addDefaultsIfNotSet()
                    ->end()
                ->end()
                ->children()
                    ->arrayNode('meta')
                        ->children()
                            ->booleanNode('enabled')
                                ->defaultNull()
                            ->end()
                        ->end()
                        ->addDefaultsIfNotSet()
                    ->end()
                ->end()
                ->addDefaultsIfNotSet()
            ->end();
    }

    private function withTracingConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('tracing')
            ->children()
                ->booleanNode('enabled')
                    ->defaultNull()
                ->end()
                ->stringNode('service')
                    ->defaultValue(TracerInterface::class)
                    ->cannotBeEmpty()
                ->end()
                ->stringNode('printer')
                    ->defaultValue(PrinterInterface::class)
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('trace_type_extraction')
                    ->defaultValue(DefaultTypeExtractorFactory::DEFAULT_TRACING_OPTION)
                ->end()
                ->booleanNode('trace_type_parse')
                    ->defaultValue(DefaultTypeParserFactory::DEFAULT_TRACING_OPTION)
                ->end()
                ->booleanNode('trace_type_fetch')
                    ->defaultValue(DefaultTypeRepositoryFactory::DEFAULT_TRACING_OPTION)
                ->end()
                ->booleanNode('trace_type_cast')
                    ->defaultValue(DefaultTypeRepositoryFactory::DEFAULT_TYPE_CAST_TRACING_OPTION)
                ->end()
                ->booleanNode('trace_type_match')
                    ->defaultValue(DefaultTypeRepositoryFactory::DEFAULT_TYPE_MATCH_TRACING_OPTION)
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }

    private function withLoggerConfiguration(NodeBuilder $config): NodeBuilder
    {
        /** @phpstan-ignore-next-line : Known Symfony's issue */
        return $config->arrayNode('logger')
            ->children()
                ->booleanNode('enabled')
                    ->defaultNull()
                ->end()
                ->stringNode('service')
                    ->defaultValue(LoggerInterface::class)
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('log_type_extraction')
                    ->defaultValue(DefaultTypeExtractorFactory::DEFAULT_LOGGING_OPTION)
                ->end()
                ->booleanNode('log_type_parse')
                    ->defaultValue(DefaultTypeParserFactory::DEFAULT_LOGGING_OPTION)
                ->end()
                ->booleanNode('log_type_fetch')
                    ->defaultValue(DefaultTypeRepositoryFactory::DEFAULT_LOGGING_OPTION)
                ->end()
                ->booleanNode('log_type_cast')
                    ->defaultValue(DefaultTypeRepositoryFactory::DEFAULT_TYPE_CAST_LOGGING_OPTION)
                ->end()
                ->booleanNode('log_type_match')
                    ->defaultValue(DefaultTypeRepositoryFactory::DEFAULT_TYPE_MATCH_LOGGING_OPTION)
                ->end()
            ->end()
            ->addDefaultsIfNotSet()
        ->end();
    }
}
