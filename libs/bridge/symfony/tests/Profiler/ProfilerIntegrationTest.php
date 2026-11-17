<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Profiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use TypeLang\Mapper\Bridge\Symfony\Profiler\MapperDataCollector;
use TypeLang\Mapper\Bridge\Symfony\Profiler\TraceableMapper;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\DecoratorStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\UserStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\TestCase;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\MapperInterface;

final class ProfilerIntegrationTest extends TestCase
{
    public function testMappersAreTracedInDebugMode(): void
    {
        $container = self::createContainer(
            config: ['mappers' => ['api' => []]],
            public: ['type_lang_mapper.mapper', 'type_lang_mapper.mapper.api', MapperInterface::class],
            isDebug: true,
        );

        self::assertInstanceOf(TraceableMapper::class, $container->get('test.type_lang_mapper.mapper'));
        self::assertInstanceOf(TraceableMapper::class, $container->get('test.type_lang_mapper.mapper.api'));
        self::assertInstanceOf(TraceableMapper::class, $container->get('test.' . MapperInterface::class));
    }

    public function testMappersAreNotTracedOutOfDebugMode(): void
    {
        $container = self::createContainer(public: ['type_lang_mapper.mapper']);

        self::assertInstanceOf(Mapper::class, $container->get('test.type_lang_mapper.mapper'));
        self::assertFalse($container->has('type_lang_mapper.data_collector'));
    }

    public function testProfilerCanBeDisabledInDebugMode(): void
    {
        $container = self::createContainer(
            config: ['profiler' => false],
            public: ['type_lang_mapper.mapper'],
            isDebug: true,
        );

        self::assertInstanceOf(Mapper::class, $container->get('test.type_lang_mapper.mapper'));
    }

    public function testCallsTypesAndMetadataAreCollected(): void
    {
        $container = self::createContainer(
            public: ['type_lang_mapper.mapper', 'type_lang_mapper.data_collector'],
            isDebug: true,
        );

        $mapper = $container->get('test.type_lang_mapper.mapper');
        $collector = $container->get('test.type_lang_mapper.data_collector');

        \assert($mapper instanceof MapperInterface && $collector instanceof MapperDataCollector);

        $mapper->denormalize(['userName' => 'admin', 'age' => 42], UserStub::class);
        $collector->lateCollect();

        self::assertCount(1, $collector->getCalls());
        self::assertSame('default', $collector->getCalls()[0]['mapper']);
        self::assertSame(UserStub::class, $collector->getCalls()[0]['type']);
        self::assertContains(UserStub::class, \array_column($collector->getTypes(), 'type'));
        self::assertSame([UserStub::class], \array_column($collector->getMetadata(), 'class'));
        self::assertGreaterThanOrEqual(1, $collector->getMetadata()[0]['reads']);
    }

    public function testTypeRecorderIsLastDecorator(): void
    {
        $container = self::createContainer(
            configure: static function (ContainerBuilder $container): void {
                $container->register(DecoratorStub::class)->setAutoconfigured(true);
            },
            isDebug: true,
            isCompiled: false,
        );

        self::assertEquals(
            [new Reference(DecoratorStub::class), new Reference('.type_lang_mapper.default.type_recorder')],
            $container->getDefinition('type_lang_mapper.mapper')->getArgument('$decorators'),
        );
    }
}
