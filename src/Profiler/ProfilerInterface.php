<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Profiler;

/**
 * Receives the calls of the traced mappers, the types they build and the
 * metadata they read, such as the profiler of a framework.
 */
interface ProfilerInterface
{
    /**
     * Called before the call is run, such as to start a timer, the call
     * is finished by {@see finishCall()}.
     *
     * @param non-empty-string $mapper
     * @param non-empty-string $method
     */
    public function startCall(string $mapper, string $method, ?string $type): void;

    public function finishCall(MapperCall $call): void;

    /**
     * @param non-empty-string $mapper
     * @param class-string $class
     */
    public function recordType(string $mapper, string $type, string $class): void;

    /**
     * @param non-empty-string $mapper
     * @param class-string $class
     * @param float $duration in milliseconds
     */
    public function recordMetadataRead(string $mapper, string $class, float $duration): void;
}
