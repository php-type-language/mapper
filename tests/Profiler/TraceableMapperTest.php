<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Profiler;

use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Mapping\MetadataBuilder;
use TypeLang\Mapper\Profiler\TraceableMapper;
use TypeLang\Mapper\Profiler\TraceableMetadataProvider;
use TypeLang\Mapper\Profiler\TypeBuildRecorder;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\HookStub;
use TypeLang\Mapper\Tests\Stub\ProfilerStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\StringType;

final class TraceableMapperTest extends TestCase
{
    public function testCallIsPassedAndReportedWithItsPlace(): void
    {
        $profiler = new ProfilerStub();
        $mapper = new TraceableMapper(new Mapper(), 'api', $profiler);

        $line = __LINE__ + 1;
        self::assertSame(42, $mapper->denormalize('42', 'int'));

        self::assertSame(['start api denormalize(int)', 'finish api denormalize(int)'], $profiler->events);

        $call = $profiler->calls[0];

        self::assertSame('42', $call->input);
        self::assertSame(42, $call->output);
        self::assertNull($call->error);
        self::assertSame([], $call->getViolations());
        self::assertSame(__FILE__, $call->file);
        self::assertSame($line, $call->line);
    }

    public function testFailedCallIsReportedWithViolations(): void
    {
        $profiler = new ProfilerStub();
        $mapper = new TraceableMapper(new Mapper(), 'default', $profiler);

        try {
            $mapper->denormalize(['old'], 'list<int>');
            self::fail('The value is denormalized');
        } catch (InvalidValueException) {
        }

        $call = $profiler->calls[0];

        self::assertInstanceOf(InvalidValueException::class, $call->error);
        self::assertSame(
            [['path' => '$[0]', 'message' => 'Value must be of type int, but "old" given']],
            $call->getViolations(),
        );
    }

    public function testViolationShowsMessageOfItsCause(): void
    {
        $profiler = new ProfilerStub();
        $hook = new HookStub(AddressStub::class, beforeDenormalize: static function (): never {
            throw new \DomainException('Address is blocked');
        });
        $mapper = new TraceableMapper(new Mapper(hooks: [$hook]), 'default', $profiler);

        try {
            $mapper->denormalize(['city' => 'Paris'], AddressStub::class);
            self::fail('The value is denormalized');
        } catch (InvalidValueException) {
        }

        self::assertSame(
            'Value array(1) is rejected: Address is blocked',
            $profiler->calls[0]->getViolations()[0]['message'],
        );
    }

    public function testBuiltTypesAndMetadataReadsAreReported(): void
    {
        $profiler = new ProfilerStub();
        $mapper = new Mapper(
            decorators: [new TypeBuildRecorder('api', $profiler)],
            metadata: new TraceableMetadataProvider(MetadataBuilder::createDefault()->build(), 'api', $profiler),
        );

        $mapper->denormalize(['city' => 'Paris'], AddressStub::class);

        self::assertContains(['api', 'string', StringType::class], $profiler->types);
        self::assertContains(['api', AddressStub::class], $profiler->metadata);
    }
}
