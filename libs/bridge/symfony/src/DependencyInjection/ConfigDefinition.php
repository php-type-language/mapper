<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Config tree of the bundle: the settings of the default mapper, the named
 * mappers with the same settings and the web profiler. A setting
 * left {@see null} is inherited, from the default mapper by a named one and
 * from the mapper itself by the default one.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Bridge\Symfony
 */
final class ConfigDefinition
{
    /**
     * Pattern of the name of a named mapper
     */
    private const MAPPER_NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    public static function define(ArrayNodeDefinition $root): void
    {
        $children = $root->children();

        self::defineSettings($children);

        $mappers = $children->arrayNode('mappers');
        $mappers->info('Mappers extending the default one, by the name');
        $mappers->useAttributeAsKey('name');
        $mappers->validate()
            ->ifTrue(static fn(array $mappers): bool => self::findInvalidName($mappers) !== null)
            ->then(static function (array $mappers): never {
                throw new \InvalidArgumentException(\sprintf(
                    'Mapper name "%s" is invalid, a name matches "%s" and is not "%s"',
                    \addcslashes((string) self::findInvalidName($mappers), '"'),
                    self::MAPPER_NAME_PATTERN,
                    MapperDefinitions::DEFAULT_NAME,
                ));
            });

        self::defineSettings($mappers->arrayPrototype()->children());

        $profiler = $children->arrayNode('profiler');
        $profiler->info('Web profiler panel of the mappers, enabled in debug mode unless disabled');
        $profiler->addDefaultsIfNotSet();
        $profiler->beforeNormalization()
            ->ifTrue(static fn(mixed $value): bool => \is_bool($value))
            ->then(static fn(bool $value): array => ['enabled' => $value]);
        $profiler->children()->booleanNode('enabled')->defaultNull();
    }

    /**
     * @param array<array-key, mixed> $mappers
     */
    private static function findInvalidName(array $mappers): ?string
    {
        foreach ($mappers as $name => $_) {
            $name = (string) $name;

            if ($name === MapperDefinitions::DEFAULT_NAME || \preg_match(self::MAPPER_NAME_PATTERN, $name) !== 1) {
                return $name;
            }
        }

        return null;
    }

    private static function defineSettings(NodeBuilder $children): void
    {
        $children->booleanNode('strict')
            ->info('Whether a value must match the type exactly, without coercion')
            ->defaultNull();

        $children->booleanNode('normalize_as_array')
            ->info('Whether an object is normalized to an array or to a stdClass')
            ->defaultNull();

        $children->integerNode('max_violations')
            ->info('Number of violations a denormalization collects before it fails')
            ->min(1)
            ->defaultNull();

        $children->booleanNode('reject_extra_fields')
            ->info('Whether a field no property takes is a violation')
            ->defaultNull();

        $children->integerNode('expected_type_depth')
            ->info('Number of levels of classes described by their fields in messages, 1 by default, 0 gives "object"')
            ->min(0)
            ->defaultNull();

        $children->integerNode('expected_type_max_fields')
            ->info('Number of fields of a class shown in messages, the others are "...", all of them in case of none')
            ->min(1)
            ->defaultNull();

        $children->scalarNode('naming')
            ->info('Built-in naming strategy (snake_case, camelCase, ...) or a service')
            ->defaultNull();

        self::defineServiceList($children, 'types', 'Type builders');
        self::defineServiceList($children, 'decorators', 'Type decorators');
        self::defineServiceList($children, 'expanders', 'Type expanders of the expected types of errors');
        self::defineServiceList($children, 'hooks', 'Class hooks');
        self::defineServiceList($children, 'instantiators', 'Instantiation strategies');
        self::defineServiceList($children, 'accessors', 'Property access strategies');

        $coercers = $children->arrayNode('coercers');
        $coercers->info('Coercers of the scalar types and of the array input, by the type');
        $coercers->addDefaultsIfNotSet();

        $coercerChildren = $coercers->children();

        foreach (MapperDefinitions::COERCED_TYPES as $type) {
            $coercerChildren->scalarNode($type)->defaultNull();
        }

        $metadata = $children->arrayNode('metadata');
        $metadata->addDefaultsIfNotSet();

        $metadataChildren = $metadata->children();

        self::defineServiceList($metadataChildren, 'readers', 'Metadata readers over the default ones');

        $paths = $metadataChildren->arrayNode('paths');
        $paths->info('Mapping config files or directories of them (yaml, neon, json, php)');
        $paths->scalarPrototype()->cannotBeEmpty();
        $paths->defaultValue([]);

        $metadataChildren->scalarNode('selector')
            ->info('Service choosing the mapped properties of a class')
            ->defaultNull();

        $cache = $metadataChildren->arrayNode('cache');
        $cache->info('Metadata cache, disabled in debug mode unless enabled');
        $cache->addDefaultsIfNotSet();
        $cache->beforeNormalization()
            ->ifTrue(static fn(mixed $value): bool => \is_bool($value))
            ->then(static fn(bool $value): array => ['enabled' => $value]);

        $cacheChildren = $cache->children();
        $cacheChildren->booleanNode('enabled')->defaultNull();

        $pools = $cacheChildren->arrayNode('pools');
        $pools->info('Caches from the first one checked to the last one, the default pool in case of none');
        $pools->defaultValue([]);

        $pool = $pools->arrayPrototype();
        $pool->validate()
            ->ifTrue(static fn(array $value): bool => ($value['psr6'] === null) === ($value['psr16'] === null))
            ->thenInvalid('A metadata cache pool requires exactly one of "psr6" and "psr16" services');

        $poolChildren = $pool->children();
        $poolChildren->scalarNode('psr6')->info('PSR-6 cache service')->defaultNull();
        $poolChildren->scalarNode('psr16')->info('PSR-16 cache service')->defaultNull();
        $poolChildren->scalarNode('prefix')
            ->info('Prefix of the cache keys, "tlm_<mapper>_" in case of none')
            ->defaultNull();
    }

    /**
     * @param non-empty-string $name
     * @param non-empty-string $info
     */
    private static function defineServiceList(NodeBuilder $children, string $name, string $info): void
    {
        $list = $children->arrayNode($name);
        $list->info($info);
        $list->scalarPrototype()->cannotBeEmpty();
        $list->defaultValue([]);
    }
}
