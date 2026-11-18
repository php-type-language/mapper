<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Profiler;

use Symfony\Component\Stopwatch\Stopwatch;
use TypeLang\Mapper\Bridge\Symfony\Profiler\MapperDataCollector;
use TypeLang\Mapper\Bridge\Symfony\Profiler\TraceableMapper;
use TypeLang\Mapper\Bridge\Symfony\Tests\TestCase;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Mapper;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\HookStub;

final class TraceableMapperTest extends TestCase
{
    public function testCallIsPassedAndCollected(): void
    {
        $collector = new MapperDataCollector();
        $mapper = new TraceableMapper(new Mapper(), 'api', $collector);

        $line = __LINE__ + 1;
        self::assertSame(42, $mapper->denormalize('42', 'int'));

        $collector->lateCollect();
        $call = $collector->getCalls()[0];

        self::assertSame('api', $call['mapper']);
        self::assertSame('denormalize', $call['method']);
        self::assertSame('int', $call['type']);
        self::assertNull($call['error']);
        self::assertSame(__FILE__, $call['file']);
        self::assertSame($line, $call['line']);
        self::assertSame(0, $collector->getErrorCount());
    }

    public function testFailedCallIsCollectedWithViolations(): void
    {
        $collector = new MapperDataCollector();
        $mapper = new TraceableMapper(new Mapper(), 'default', $collector);

        try {
            $mapper->denormalize(['old'], 'list<int>');
            self::fail('The value is denormalized');
        } catch (InvalidValueException) {
        }

        $collector->lateCollect();
        $call = $collector->getCalls()[0];

        self::assertSame(InvalidValueException::class, $call['error']['class'] ?? null);
        self::assertSame('$[0]', $call['violations'][0]['path']);
        self::assertSame('Value must be of type int, but "old" given', $call['violations'][0]['message']);
        self::assertSame(1, $collector->getErrorCount());
    }

    public function testViolationShowsMessageOfItsCause(): void
    {
        $collector = new MapperDataCollector();
        $hook = new HookStub(AddressStub::class, beforeDenormalize: static function (): never {
            throw new \DomainException('Address is blocked');
        });
        $mapper = new TraceableMapper(new Mapper(hooks: [$hook]), 'default', $collector);

        try {
            $mapper->denormalize(['city' => 'Paris'], AddressStub::class);
            self::fail('The value is denormalized');
        } catch (InvalidValueException) {
        }

        $collector->lateCollect();

        self::assertSame(
            'Value array(1) is rejected: Address is blocked',
            $collector->getCalls()[0]['violations'][0]['message'],
        );
    }

    public function testCallIsReportedToStopwatch(): void
    {
        $stopwatch = new Stopwatch();
        $mapper = new TraceableMapper(new Mapper(), 'default', new MapperDataCollector(), $stopwatch);

        $mapper->normalize(42, 'int');

        self::assertTrue($stopwatch->isStarted('normalize(int)') === false);
        self::assertSame('type_lang_mapper', $stopwatch->getEvent('normalize(int)')->getCategory());
    }

    public function testResetForgetsCalls(): void
    {
        $collector = new MapperDataCollector();
        $mapper = new TraceableMapper(new Mapper(), 'default', $collector);

        $mapper->isNormalizable(42, 'int');
        $collector->reset();
        $collector->lateCollect();

        self::assertSame([], $collector->getCalls());
    }
}
