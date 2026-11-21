<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Passes the extensions to the mappers. The extensions of a kind of a
 * named mapper are its own ones, then the ones of the default mapper. The
 * extensions of a mapper are the ones of its config in the order of the
 * config, then the tagged ones by the priority. A service tagged without
 * the `mapper` attribute extends the default mapper. A coercer of a type
 * of a named mapper replaces the one of the default mapper. The type
 * recorder of the profiler is the last decorator, it sees the final type.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Bridge\Symfony
 */
final class MapperExtensionsPass implements CompilerPassInterface
{
    /**
     * @throws InvalidArgumentException in case of a service is tagged for a
     *         mapper that is not configured
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(MapperDefinitions::EXTENSIONS_PARAMETER)) {
            return;
        }

        /** @var array<non-empty-string, array<value-of<ExtensionKind>, list<non-empty-string>>> */
        $services = $container->getParameter(MapperDefinitions::EXTENSIONS_PARAMETER);
        $container->getParameterBag()->remove(MapperDefinitions::EXTENSIONS_PARAMETER);

        foreach (ExtensionKind::cases() as $kind) {
            foreach ($this->findTaggedServices($container, $kind, \array_keys($services)) as $name => $ids) {
                $services[$name][$kind->value] = [...$services[$name][$kind->value], ...$ids];
            }
        }

        $defaults = $services[MapperDefinitions::DEFAULT_NAME];

        foreach ($services as $name => $own) {
            foreach (ExtensionKind::cases() as $kind) {
                $ids = $own[$kind->value];

                if ($name !== MapperDefinitions::DEFAULT_NAME) {
                    $ids = [...$ids, ...$defaults[$kind->value]];
                }

                $references = [];

                foreach (\array_unique($ids) as $id) {
                    $references[] = new Reference($id);
                }

                $recorder = MapperDefinitions::getTypeRecorderId($name);

                if ($kind === ExtensionKind::TypeDecorator && $container->hasDefinition($recorder)) {
                    $references[] = new Reference($recorder);
                }

                $kind->addServices(
                    $container->getDefinition(MapperDefinitions::getExtensionTargetId($name, $kind)),
                    $references,
                );
            }
        }

        $this->processCoercers($container, \array_keys($services));
    }

    /**
     * Passes the coercers to the mappers: the own coercer of a type, then
     * the one of the default mapper, then the default of the core.
     *
     * @param list<non-empty-string> $mappers
     * @throws InvalidArgumentException in case of a mapper is given two
     *         coercers of one type or a coercer of an unknown type
     */
    private function processCoercers(ContainerBuilder $container, array $mappers): void
    {
        /** @var array<non-empty-string, array<non-empty-string, non-empty-string>> */
        $coercers = $container->getParameter(MapperDefinitions::COERCERS_PARAMETER);
        $container->getParameterBag()->remove(MapperDefinitions::COERCERS_PARAMETER);

        /** @var array<non-empty-string, list<array<array-key, mixed>>> */
        $tagged = $container->findTaggedServiceIds(MapperDefinitions::COERCER_TAG);

        foreach ($tagged as $id => $tags) {
            foreach ($tags as $attributes) {
                $mapper = self::getTaggedMapper($id, MapperDefinitions::COERCER_TAG, $attributes, $mappers);
                $type = $attributes['type'] ?? null;

                if (!\is_string($type) || !\in_array($type, MapperDefinitions::COERCED_TYPES, true)) {
                    throw new InvalidArgumentException(\sprintf(
                        'Service "%s" is tagged "%s" with type "%s", supported types are %s',
                        \addcslashes($id, '"'),
                        MapperDefinitions::COERCER_TAG,
                        \addcslashes(\is_string($type) ? $type : \get_debug_type($type), '"'),
                        \implode(', ', MapperDefinitions::COERCED_TYPES),
                    ));
                }

                if (isset($coercers[$mapper][$type])) {
                    throw new InvalidArgumentException(\sprintf(
                        'Mapper "%s" is given two coercers of type "%s": "%s" and "%s"',
                        $mapper,
                        $type,
                        \addcslashes($coercers[$mapper][$type], '"'),
                        \addcslashes($id, '"'),
                    ));
                }

                $coercers[$mapper][$type] = $id;
            }
        }

        $defaults = $coercers[MapperDefinitions::DEFAULT_NAME] ?? [];

        foreach ($mappers as $name) {
            $arguments = [];

            foreach ([...$defaults, ...$coercers[$name] ?? []] as $type => $id) {
                $arguments[$type] = new Reference($id);
            }

            $container->getDefinition(MapperDefinitions::getMapperId($name))
                ->setArgument('$coercers', $arguments);
        }
    }

    /**
     * Returns the mapper of the tag, the default one in case of the tag
     * names none.
     *
     * @param array<array-key, mixed> $attributes
     * @param list<non-empty-string> $mappers
     * @return non-empty-string
     * @throws InvalidArgumentException in case of the mapper is not
     *         configured
     */
    private static function getTaggedMapper(string $id, string $tag, array $attributes, array $mappers): string
    {
        $mapper = $attributes['mapper'] ?? MapperDefinitions::DEFAULT_NAME;

        if (\is_string($mapper) && \in_array($mapper, $mappers, true)) {
            return $mapper;
        }

        throw new InvalidArgumentException(\sprintf(
            'Service "%s" is tagged "%s" for mapper "%s" that is not configured',
            \addcslashes($id, '"'),
            $tag,
            \addcslashes(\is_string($mapper) ? $mapper : \get_debug_type($mapper), '"'),
        ));
    }

    /**
     * Returns the ids of the services tagged with the kind by the name of
     * the mapper, sorted by the priority.
     *
     * @param list<non-empty-string> $mappers
     * @return array<non-empty-string, list<non-empty-string>>
     * @throws InvalidArgumentException in case of a service is tagged for a
     *         mapper that is not configured
     */
    private function findTaggedServices(ContainerBuilder $container, ExtensionKind $kind, array $mappers): array
    {
        $found = [];

        /** @var array<non-empty-string, list<array<array-key, mixed>>> */
        $tagged = $container->findTaggedServiceIds($kind->value);

        foreach ($tagged as $id => $tags) {
            foreach ($tags as $attributes) {
                $mapper = self::getTaggedMapper($id, $kind->value, $attributes, $mappers);
                $priority = $attributes['priority'] ?? 0;

                $found[$mapper][] = ['id' => $id, 'priority' => \is_int($priority) ? $priority : 0];
            }
        }

        $result = [];

        foreach ($found as $mapper => $entries) {
            \usort($entries, static fn(array $a, array $b): int => $b['priority'] <=> $a['priority']);

            foreach ($entries as $entry) {
                $result[$mapper][] = $entry['id'];
            }
        }

        return $result;
    }
}
