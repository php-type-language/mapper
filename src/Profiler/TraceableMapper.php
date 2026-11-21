<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Profiler;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\MapperInterface;

/**
 * Passes the calls of a mapper to the delegate and reports each of them to
 * the profiler.
 */
final class TraceableMapper implements MapperInterface
{
    public function __construct(
        private readonly MapperInterface $delegate,
        /**
         * Name of the mapper in the profiler
         *
         * @var non-empty-string
         */
        private readonly string $name,
        private readonly ProfilerInterface $profiler,
    ) {}

    public function denormalize(mixed $value, #[Language('PHP')] string $type): mixed
    {
        return $this->trace(__FUNCTION__, $type, $value, fn(): mixed => $this->delegate->denormalize($value, $type));
    }

    public function isDenormalizable(mixed $value, #[Language('PHP')] string $type): bool
    {
        return (bool) $this->trace(
            __FUNCTION__,
            $type,
            $value,
            fn(): bool => $this->delegate->isDenormalizable($value, $type),
        );
    }

    public function normalize(mixed $value, #[Language('PHP')] ?string $type = null): mixed
    {
        return $this->trace(__FUNCTION__, $type, $value, fn(): mixed => $this->delegate->normalize($value, $type));
    }

    public function isNormalizable(mixed $value, #[Language('PHP')] ?string $type = null): bool
    {
        return (bool) $this->trace(
            __FUNCTION__,
            $type,
            $value,
            fn(): bool => $this->delegate->isNormalizable($value, $type),
        );
    }

    /**
     * Runs the call, reports it with the place of the call in the code of
     * the user, the frame above the public method.
     *
     * @param non-empty-string $method
     * @param \Closure(): mixed $call
     */
    private function trace(string $method, ?string $type, mixed $value, \Closure $call): mixed
    {
        $caller = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? [];

        $this->profiler->startCall($this->name, $method, $type);

        $startedAt = \hrtime(true);
        $result = null;
        $error = null;

        try {
            $result = $call();
        } catch (\Throwable $e) {
            $error = $e;

            throw $e;
        } finally {
            $this->profiler->finishCall(new MapperCall(
                mapper: $this->name,
                method: $method,
                type: $type,
                input: $value,
                output: $result,
                error: $error,
                duration: (\hrtime(true) - $startedAt) / 1e6,
                file: $caller['file'] ?? null,
                line: $caller['line'] ?? null,
            ));
        }

        return $result;
    }
}
