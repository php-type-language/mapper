<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Profiler;

use JetBrains\PhpStorm\Language;
use Symfony\Component\Stopwatch\Stopwatch;
use TypeLang\Mapper\MapperInterface;

/**
 * Passes the calls of a mapper to the delegate and reports each of them to
 * the collector and to the stopwatch, as the decorator of the mapper
 * service in the debug mode.
 */
final class TraceableMapper implements MapperInterface
{
    /**
     * Category of the stopwatch events
     */
    private const STOPWATCH_CATEGORY = 'type_lang_mapper';

    public function __construct(
        private readonly MapperInterface $delegate,
        /**
         * Name of the mapper in the profiler
         *
         * @var non-empty-string
         */
        private readonly string $name,
        private readonly MapperDataCollector $collector,
        private readonly ?Stopwatch $stopwatch = null,
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
        $event = $this->stopwatch?->start(\sprintf('%s(%s)', $method, $type ?? 'mixed'), self::STOPWATCH_CATEGORY);
        $startedAt = \hrtime(true);
        $result = null;
        $error = null;

        try {
            $result = $call();
        } catch (\Throwable $e) {
            $error = $e;

            throw $e;
        } finally {
            $event?->stop();

            $this->collector->collectCall(
                mapper: $this->name,
                method: $method,
                type: $type,
                input: $value,
                output: $result,
                error: $error,
                duration: (\hrtime(true) - $startedAt) / 1e6,
                file: $caller['file'] ?? null,
                line: $caller['line'] ?? null,
            );
        }

        return $result;
    }
}
