<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperAccessor;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperDecorator;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperHook;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperInstantiator;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperMetadataReader;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperTypeBuilder;
use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperTypeExpander;
use TypeLang\Mapper\Bridge\Symfony\Attribute\MapperExtensionAttribute;
use TypeLang\Mapper\Hook\ClassHookInterface;
use TypeLang\Mapper\Instantiator\Strategy\InstantiationStrategyInterface;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\PropertyAccessor\Strategy\AccessStrategyInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\Expander\TypeExpanderInterface;

/**
 * Role of a service extending a mapper, the value is the tag of the
 * service.
 *
 * @phpstan-import-type MapperSettingsType from MapperDefinitions
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Bridge\Symfony
 */
enum ExtensionKind: string
{
    case TypeBuilder = 'type_lang_mapper.type_builder';
    case TypeDecorator = 'type_lang_mapper.type_decorator';
    case TypeExpander = 'type_lang_mapper.type_expander';
    case ClassHook = 'type_lang_mapper.class_hook';
    case InstantiationStrategy = 'type_lang_mapper.instantiation_strategy';
    case AccessStrategy = 'type_lang_mapper.access_strategy';
    case MetadataReader = 'type_lang_mapper.metadata_reader';

    /**
     * Returns the attribute autoconfiguring a service of the kind.
     *
     * @return class-string<MapperExtensionAttribute>
     */
    public function getAttribute(): string
    {
        return match ($this) {
            self::TypeBuilder => AsMapperTypeBuilder::class,
            self::TypeDecorator => AsMapperDecorator::class,
            self::TypeExpander => AsMapperTypeExpander::class,
            self::ClassHook => AsMapperHook::class,
            self::InstantiationStrategy => AsMapperInstantiator::class,
            self::AccessStrategy => AsMapperAccessor::class,
            self::MetadataReader => AsMapperMetadataReader::class,
        };
    }

    /**
     * @return class-string
     */
    public function getInterface(): string
    {
        return match ($this) {
            self::TypeBuilder => TypeBuilderInterface::class,
            self::TypeDecorator => TypeDecoratorInterface::class,
            self::TypeExpander => TypeExpanderInterface::class,
            self::ClassHook => ClassHookInterface::class,
            self::InstantiationStrategy => InstantiationStrategyInterface::class,
            self::AccessStrategy => AccessStrategyInterface::class,
            self::MetadataReader => MetadataReaderInterface::class,
        };
    }

    /**
     * Returns the services of the kind listed in the settings of a mapper.
     *
     * @param MapperSettingsType $settings
     * @return list<non-empty-string>
     */
    public function getConfiguredServices(array $settings): array
    {
        return match ($this) {
            self::TypeBuilder => $settings['types'],
            self::TypeDecorator => $settings['decorators'],
            self::TypeExpander => $settings['expanders'],
            self::ClassHook => $settings['hooks'],
            self::InstantiationStrategy => $settings['instantiators'],
            self::AccessStrategy => $settings['accessors'],
            self::MetadataReader => $settings['metadata']['readers'],
        };
    }

    /**
     * Passes the extensions to the service taking them: an argument of the
     * mapper, the readers are added to the metadata builder over the default
     * ones in the order of the list.
     *
     * @param list<Reference> $references
     */
    public function addServices(Definition $definition, array $references): void
    {
        $argument = match ($this) {
            self::TypeBuilder => '$builders',
            self::TypeDecorator => '$decorators',
            self::TypeExpander => '$expanders',
            self::ClassHook => '$hooks',
            self::InstantiationStrategy => '$instantiators',
            self::AccessStrategy => '$accessors',
            self::MetadataReader => null,
        };

        if ($argument !== null) {
            $definition->setArgument($argument, $references);

            return;
        }

        foreach (\array_reverse($references) as $reference) {
            $definition->addMethodCall('withAddedReader', [$reference, \PHP_INT_MAX], true);
        }
    }
}
