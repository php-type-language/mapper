<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\AccessorStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\ApiShoutTypeBuilderStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\DecimalCommaCoercerStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\DecoratorStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\ExpanderStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\GlobalShoutTypeBuilderStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\HookStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\InstantiatorStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\MetadataReaderStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\MisplacedHookStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\ShoutTypeBuilderStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\UserStub;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\DenormalizerInterface;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\MapperInterface;
use TypeLang\Mapper\NormalizerInterface;

final class TypeLangMapperBundleTest extends TestCase
{
    public function testDefaultMapperIsRegisteredWithItsInterfaces(): void
    {
        $container = self::createContainer(public: [
            'type_lang_mapper.mapper',
            MapperInterface::class,
            DenormalizerInterface::class,
            NormalizerInterface::class,
        ]);

        $mapper = $container->get('test.type_lang_mapper.mapper');

        self::assertInstanceOf(Mapper::class, $mapper);
        self::assertSame($mapper, $container->get('test.' . MapperInterface::class));
        self::assertSame($mapper, $container->get('test.' . DenormalizerInterface::class));
        self::assertSame($mapper, $container->get('test.' . NormalizerInterface::class));
        self::assertSame(42, $mapper->denormalize('42', 'int'));
    }

    public function testNamedMapperIsRegistered(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            public: ['type_lang_mapper.mapper.api', 'type_lang_mapper.mapper'],
        );

        self::assertNotSame(
            $container->get('test.type_lang_mapper.mapper'),
            $container->get('test.type_lang_mapper.mapper.api'),
        );
    }

    public function testNamedMapperHasArgumentAliases(): void
    {
        $container = self::createContainer(config: ['mappers' => ['api' => []]], isCompiled: false);

        foreach ([MapperInterface::class, DenormalizerInterface::class, NormalizerInterface::class] as $type) {
            self::assertSame(
                'type_lang_mapper.mapper.api',
                (string) $container->getAlias($type . ' $apiMapper'),
            );
        }
    }

    public function testNamedMapperInheritsAndOverridesSettings(): void
    {
        $container = self::createContainer(
            config: [
                'naming' => 'snake_case',
                'mappers' => ['api' => ['strict' => true]],
            ],
            public: ['type_lang_mapper.mapper.api', 'type_lang_mapper.mapper'],
        );

        $default = $container->get('test.type_lang_mapper.mapper');
        $api = $container->get('test.type_lang_mapper.mapper.api');

        \assert($default instanceof Mapper && $api instanceof Mapper);

        self::assertSame(42, $default->denormalize('42', 'int'));
        self::assertSame(['user_name' => 'admin', 'age' => 42], $api->normalize(self::createUser()));

        $this->expectException(InvalidValueException::class);

        $api->denormalize('42', 'int');
    }

    public function testOptionsArePassedToConfiguration(): void
    {
        $container = self::createContainer(
            config: [
                'strict' => true,
                'normalize_as_array' => false,
                'max_violations' => 5,
                'reject_extra_fields' => true,
                'expected_type_depth' => 3,
                'expected_type_max_fields' => 7,
                'mappers' => ['api' => ['max_violations' => 9, 'expected_type_max_fields' => 2]],
            ],
            public: ['.type_lang_mapper.default.configuration', '.type_lang_mapper.api.configuration'],
        );

        $default = $container->get('test..type_lang_mapper.default.configuration');
        $api = $container->get('test..type_lang_mapper.api.configuration');

        self::assertEquals(new Configuration(
            strict: true,
            normalizeAsArray: false,
            maxViolations: 5,
            rejectExtraFields: true,
            expectedTypeDepth: 3,
            expectedTypeMaxFields: 7,
        ), $default);

        self::assertEquals(new Configuration(
            strict: true,
            normalizeAsArray: false,
            maxViolations: 9,
            rejectExtraFields: true,
            expectedTypeDepth: 3,
            expectedTypeMaxFields: 2,
        ), $api);
    }

    public function testConfiguredTypeOfNamedMapperIsNotInDefaultOne(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => ['types' => ['app.shout']]]],
            public: ['type_lang_mapper.mapper.api', 'type_lang_mapper.mapper'],
            configure: static function (ContainerBuilder $container): void {
                $container->register('app.shout', ShoutTypeBuilderStub::class);
            },
        );

        $api = $container->get('test.type_lang_mapper.mapper.api');
        $default = $container->get('test.type_lang_mapper.mapper');

        \assert($default instanceof Mapper && $api instanceof Mapper);

        self::assertSame('hey', $api->denormalize('hey', 'shout'));

        $this->expectException(TypeDefinitionException::class);

        $default->denormalize('hey', 'shout');
    }

    public function testConfiguredTypeOfDefaultMapperIsInherited(): void
    {
        $container = self::createContainer(
            config: ['types' => ['app.shout'], 'mappers' => ['api' => []]],
            public: ['type_lang_mapper.mapper.api'],
            configure: static function (ContainerBuilder $container): void {
                $container->register('app.shout', ShoutTypeBuilderStub::class);
            },
        );

        $api = $container->get('test.type_lang_mapper.mapper.api');

        \assert($api instanceof Mapper);

        self::assertSame('hey', $api->denormalize('hey', 'shout'));
    }

    public function testAttributeWithoutMappersExtendsAllMappers(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            public: ['type_lang_mapper.mapper.api', 'type_lang_mapper.mapper'],
            configure: static function (ContainerBuilder $container): void {
                $container->register(GlobalShoutTypeBuilderStub::class)->setAutoconfigured(true);
            },
        );

        foreach (['type_lang_mapper.mapper', 'type_lang_mapper.mapper.api'] as $id) {
            $mapper = $container->get('test.' . $id);

            \assert($mapper instanceof Mapper);

            self::assertSame('hey', $mapper->denormalize('hey', 'shout'));
        }
    }

    public function testAttributeWithMappersExtendsThemOnly(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            public: ['type_lang_mapper.mapper.api', 'type_lang_mapper.mapper'],
            configure: static function (ContainerBuilder $container): void {
                $container->register(ApiShoutTypeBuilderStub::class)->setAutoconfigured(true);
            },
        );

        $api = $container->get('test.type_lang_mapper.mapper.api');
        $default = $container->get('test.type_lang_mapper.mapper');

        \assert($default instanceof Mapper && $api instanceof Mapper);

        self::assertSame('hey', $api->denormalize('hey', 'shout'));

        $this->expectException(TypeDefinitionException::class);

        $default->denormalize('hey', 'shout');
    }

    /**
     * @return iterable<non-empty-string, array{class-string, non-empty-string, non-empty-string}>
     */
    public static function attributeDataProvider(): iterable
    {
        yield 'decorator' => [DecoratorStub::class, 'type_lang_mapper.mapper', '$decorators'];
        yield 'expander' => [ExpanderStub::class, 'type_lang_mapper.mapper', '$expanders'];
        yield 'hook' => [HookStub::class, 'type_lang_mapper.mapper.api', '$hooks'];
        yield 'instantiator' => [InstantiatorStub::class, 'type_lang_mapper.mapper', '$instantiators'];
        yield 'accessor' => [AccessorStub::class, 'type_lang_mapper.mapper', '$accessors'];
    }

    /**
     * @param class-string $class
     * @param non-empty-string $target
     * @param non-empty-string $argument
     */
    #[DataProvider('attributeDataProvider')]
    public function testAttributeAddsServiceToItsRole(string $class, string $target, string $argument): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            configure: static function (ContainerBuilder $container) use ($class): void {
                $container->register($class)->setAutoconfigured(true);
            },
            isCompiled: false,
        );

        self::assertEquals([new Reference($class)], $container->getDefinition($target)->getArgument($argument));
    }

    public function testMetadataReaderAttributeAddsReaderOverDefaultOnes(): void
    {
        $container = self::createContainer(
            configure: static function (ContainerBuilder $container): void {
                $container->register(MetadataReaderStub::class)->setAutoconfigured(true);
            },
            isCompiled: false,
        );

        self::assertContainsEquals(
            ['withAddedReader', [new Reference(MetadataReaderStub::class), \PHP_INT_MAX], true],
            $container->getDefinition('.type_lang_mapper.default.metadata_builder')->getMethodCalls(),
        );
    }

    public function testHookForNamedMapperIsNotInDefaultOne(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            configure: static function (ContainerBuilder $container): void {
                $container->register(HookStub::class)->setAutoconfigured(true);
            },
            isCompiled: false,
        );

        self::assertSame([], $container->getDefinition('type_lang_mapper.mapper')->getArgument('$hooks'));
    }

    public function testAttributeOnClassOfAnotherRoleIsRejected(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('does not implement');

        self::createContainer(configure: static function (ContainerBuilder $container): void {
            $container->register(MisplacedHookStub::class)->setAutoconfigured(true);
        });
    }

    public function testCoercerAttributeSetsCoercerOfType(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            public: ['type_lang_mapper.mapper.api', 'type_lang_mapper.mapper'],
            configure: static function (ContainerBuilder $container): void {
                $container->register(DecimalCommaCoercerStub::class)->setAutoconfigured(true);
            },
        );

        $api = $container->get('test.type_lang_mapper.mapper.api');
        $default = $container->get('test.type_lang_mapper.mapper');

        \assert($default instanceof Mapper && $api instanceof Mapper);

        self::assertSame(4.2, $api->denormalize('4,2', 'float'));

        $this->expectException(InvalidValueException::class);

        $default->denormalize('4,2', 'float');
    }

    public function testCoercerOfDefaultMapperIsInherited(): void
    {
        $container = self::createContainer(
            config: ['coercers' => ['float' => 'app.coercer'], 'mappers' => ['api' => []]],
            configure: static function (ContainerBuilder $container): void {
                $container->register('app.coercer', DecimalCommaCoercerStub::class);
            },
            isCompiled: false,
        );

        self::assertEquals(
            ['float' => new Reference('app.coercer')],
            $container->getDefinition('type_lang_mapper.mapper.api')->getArgument('$coercers'),
        );
    }

    public function testTwoCoercersOfOneTypeAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is given two coercers of type "float"');

        self::createContainer(
            config: ['mappers' => ['api' => ['coercers' => ['float' => 'app.coercer']]]],
            configure: static function (ContainerBuilder $container): void {
                $container->register('app.coercer', DecimalCommaCoercerStub::class);
                $container->register(DecimalCommaCoercerStub::class)->setAutoconfigured(true);
            },
        );
    }

    public function testNamedExtensionsGoBeforeDefaultOnes(): void
    {
        $container = self::createContainer(
            config: ['types' => ['app.global'], 'mappers' => ['api' => ['types' => ['app.own']]]],
            configure: static function (ContainerBuilder $container): void {
                $container->register('app.global', ShoutTypeBuilderStub::class);
                $container->register('app.own', ShoutTypeBuilderStub::class);
                $container->register('app.tagged', ShoutTypeBuilderStub::class)
                    ->addTag('type_lang_mapper.type_builder', ['mapper' => 'api', 'priority' => 10]);
            },
            isCompiled: false,
        );

        $builders = $container->getDefinition('type_lang_mapper.mapper.api')->getArgument('$builders');

        self::assertEquals(
            [new Reference('app.own'), new Reference('app.tagged'), new Reference('app.global')],
            $builders,
        );
    }

    public function testServiceTaggedForUnknownMapperIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('for mapper "admin" that is not configured');

        self::createContainer(configure: static function (ContainerBuilder $container): void {
            $container->register('app.shout', ShoutTypeBuilderStub::class)
                ->addTag('type_lang_mapper.type_builder', ['mapper' => 'admin']);
        });
    }

    public function testMapperNamedDefaultIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        self::createContainer(config: ['mappers' => ['default' => []]]);
    }

    public function testMapperNameOutOfPatternIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        self::createContainer(config: ['mappers' => ['api.v2' => []]]);
    }

    public function testCacheIsDisabledInDebugMode(): void
    {
        $container = self::createContainer(isDebug: true, isCompiled: false);

        self::assertSame([], self::getCacheCalls($container, 'default'));
    }

    public function testCacheUsesDefaultPoolIfItIsDeclared(): void
    {
        $container = self::createContainer(config: ['mappers' => ['api' => []]], isCompiled: false);
        $pool = new Reference('cache.type_lang_mapper', ContainerInterface::IGNORE_ON_INVALID_REFERENCE);

        self::assertEquals([['withPsr6Cache', [$pool, 'tlm_default_']]], self::getCacheCalls($container, 'default'));
        self::assertEquals([['withPsr6Cache', [$pool, 'tlm_api_']]], self::getCacheCalls($container, 'api'));
    }

    public function testCacheEnabledInConfigRequiresPool(): void
    {
        $container = self::createContainer(
            config: ['metadata' => ['cache' => true]],
            isDebug: true,
            isCompiled: false,
        );

        self::assertEquals(
            [['withPsr6Cache', [new Reference('cache.type_lang_mapper'), 'tlm_default_']]],
            self::getCacheCalls($container, 'default'),
        );
    }

    public function testCachePoolsAreAddedInOrderOfConfig(): void
    {
        $container = self::createContainer(
            config: ['metadata' => ['cache' => ['pools' => [
                ['psr16' => 'app.redis', 'prefix' => 'hot_'],
                ['psr6' => 'app.files'],
            ]]]],
            isCompiled: false,
        );

        self::assertEquals([
            ['withPsr16Cache', [new Reference('app.redis'), 'hot_']],
            ['withPsr6Cache', [new Reference('app.files'), 'tlm_default_']],
        ], self::getCacheCalls($container, 'default'));
    }

    public function testNamedMapperOverridesCachePools(): void
    {
        $container = self::createContainer(
            config: [
                'metadata' => ['cache' => ['pools' => [['psr6' => 'app.files']]]],
                'mappers' => ['api' => ['metadata' => ['cache' => ['pools' => [['psr16' => 'app.redis']]]]]],
            ],
            isCompiled: false,
        );

        self::assertEquals(
            [['withPsr16Cache', [new Reference('app.redis'), 'tlm_api_']]],
            self::getCacheCalls($container, 'api'),
        );
    }

    public function testCachePoolOfBothStandardsIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        self::createContainer(config: ['metadata' => ['cache' => ['pools' => [['psr6' => 'a', 'psr16' => 'b']]]]]);
    }

    /**
     * @param non-empty-string $mapper
     * @return list<array{string, array<array-key, mixed>}>
     */
    private static function getCacheCalls(ContainerBuilder $container, string $mapper): array
    {
        $result = [];

        $definition = $container->getDefinition('.type_lang_mapper.' . $mapper . '.metadata_builder');

        foreach ($definition->getMethodCalls() as [$method, $arguments]) {
            if (\str_ends_with($method, 'Cache')) {
                $result[] = [$method, $arguments];
            }
        }

        return $result;
    }

    public function testMappingConfigDirectoryIsRead(): void
    {
        $container = self::createContainer(
            config: ['metadata' => ['paths' => ['%kernel.project_dir%/Stub/config']]],
            public: ['type_lang_mapper.mapper'],
        );

        $mapper = $container->get('test.type_lang_mapper.mapper');

        \assert($mapper instanceof Mapper);

        self::assertSame(['login' => 'admin', 'age' => 42], $mapper->normalize(self::createUser()));
    }

    public function testMissingMappingConfigPathIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('does not exist');

        self::createContainer(config: ['metadata' => ['paths' => [__DIR__ . '/missing']]]);
    }

    private static function createUser(): UserStub
    {
        $user = new UserStub();
        $user->userName = 'admin';
        $user->age = 42;

        return $user;
    }
}
