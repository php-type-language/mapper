<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Profiler\MapperCall;
use TypeLang\Mapper\Profiler\ProfilerInterface;

final class ProfilerStub implements ProfilerInterface
{
    /**
     * Started and finished calls in the order of the reports
     *
     * @var list<non-empty-string>
     */
    public array $events = [];

    /**
     * @var list<MapperCall>
     */
    public array $calls = [];

    /**
     * @var list<array{non-empty-string, string, class-string}>
     */
    public array $types = [];

    /**
     * @var list<array{non-empty-string, class-string}>
     */
    public array $metadata = [];

    public function startCall(string $mapper, string $method, ?string $type): void
    {
        $this->events[] = \sprintf('start %s %s(%s)', $mapper, $method, $type ?? 'mixed');
    }

    public function finishCall(MapperCall $call): void
    {
        $this->events[] = \sprintf('finish %s %s(%s)', $call->mapper, $call->method, $call->type ?? 'mixed');
        $this->calls[] = $call;
    }

    public function recordType(string $mapper, string $type, string $class): void
    {
        $this->types[] = [$mapper, $type, $class];
    }

    public function recordMetadataRead(string $mapper, string $class, float $duration): void
    {
        $this->metadata[] = [$mapper, $class];
    }
}
