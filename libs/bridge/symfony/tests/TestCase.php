<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\DependencyInjection\Compiler\AttributeAutoconfigurationPass;
use Symfony\Component\DependencyInjection\Compiler\MergeExtensionConfigurationPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveClassPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use TypeLang\Mapper\Bridge\Symfony\DependencyInjection\MapperExtensionsPass;
use TypeLang\Mapper\Bridge\Symfony\TypeLangMapperBundle;

#[Group('unit'), Group('type-lang/mapper')]
abstract class TestCase extends BaseTestCase
{
    /**
     * Creates a container with the bundle loaded from the config, the
     * callback registers the services of the test before the compilation.
     * The given services are made public.
     *
     * @param array<array-key, mixed> $config
     * @param list<non-empty-string> $public
     * @param \Closure(ContainerBuilder): void|null $configure
     */
    protected static function createContainer(
        array $config = [],
        array $public = [],
        ?\Closure $configure = null,
        bool $isDebug = false,
        bool $isCompiled = true,
    ): ContainerBuilder {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.debug' => $isDebug,
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__,
            'kernel.project_dir' => __DIR__,
        ]));

        $bundle = new TypeLangMapperBundle();
        $extension = $bundle->getContainerExtension();

        \assert($extension !== null);

        $container->registerExtension($extension);
        $container->loadFromExtension($extension->getAlias(), $config);
        $bundle->build($container);

        if ($configure !== null) {
            $configure($container);
        }

        foreach ($public as $id) {
            $container->setAlias('test.' . $id, $id)->setPublic(true);
        }

        if ($isCompiled) {
            $container->compile();

            return $container;
        }

        (new MergeExtensionConfigurationPass())->process($container);
        (new ResolveClassPass())->process($container);
        (new AttributeAutoconfigurationPass())->process($container);
        (new ResolveInstanceofConditionalsPass())->process($container);
        (new MapperExtensionsPass())->process($container);

        return $container;
    }
}
