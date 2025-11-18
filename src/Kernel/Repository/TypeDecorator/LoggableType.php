<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Repository\TypeDecorator;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Context\RuntimeContext;
use TypeLang\Mapper\Type\TypeInterface;

/**
 * Decorates calls of each type by adding logging functionality
 *
 * @template-covariant TResult of mixed = mixed
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Type\Repository
 *
 * @template-extends TypeDecorator<TResult>
 */
final class LoggableType extends TypeDecorator
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_CAST_GROUP_NAME = 'CAST';

    /**
     * @var non-empty-string
     */
    public const DEFAULT_MATCH_GROUP_NAME = 'MATCH';

    /**
     * @param TypeInterface<TResult> $delegate
     */
    public function __construct(
        TypeInterface $delegate,
        /**
         * @var non-empty-string
         */
        private readonly string $cast = self::DEFAULT_CAST_GROUP_NAME,
        /**
         * @var non-empty-string
         */
        private readonly string $match = self::DEFAULT_MATCH_GROUP_NAME,
    ) {
        parent::__construct($delegate);
    }

    /**
     * @return non-empty-string
     */
    private function getInstanceName(object $entry): string
    {
        return $entry::class . '#' . \spl_object_id($entry);
    }

    /**
     * @return list<string>
     */
    private function getPathAsStringArray(RuntimeContext $context): array
    {
        $result = [];

        foreach ($context->getPath() as $entry) {
            $result[] = (string) $entry;
        }

        return $result;
    }

    /**
     * @return array<array-key, mixed>
     */
    private function getLoggerArguments(mixed $value, RuntimeContext $context, string $group): array
    {
        $realType = $this->getDecoratedType();

        return [
            'value' => $value,
            'type_name' => $this->getInstanceName($realType),
            'path' => $this->getPathAsStringArray($context),
            'type' => $realType,
            'group' => $group,
        ];
    }

    private function logBeforeMatch(LoggerInterface $logger, mixed $value, RuntimeContext $context): void
    {
        $logger->debug('[{group}] Matching {value} by {type_name} type', [
            ...$this->getLoggerArguments($value, $context, $this->match),
        ]);
    }

    private function logAfterMatch(LoggerInterface $logger, mixed $value, RuntimeContext $context, bool $status): void
    {
        $logger->info('[{group}] {match_verbose} {value} by {type_name} type', [
            'match' => $status,
            'match_verbose' => $status ? '✔ Matched' : '✘ Not matched',
            ...$this->getLoggerArguments($value, $context, $this->match),
        ]);
    }

    private function logErrorMatch(LoggerInterface $logger, mixed $value, RuntimeContext $context, \Throwable $e): void
    {
        $logger->info('[{group}] Match error: {message}', [
            'error' => $e->getMessage(),
            ...$this->getLoggerArguments($value, $context, $this->match),
        ]);
    }

    public function match(mixed $value, RuntimeContext $context): bool
    {
        $logger = $context->config->findLogger();

        if ($logger === null) {
            return parent::match($value, $context);
        }

        return $this->matchThroughLogger($logger, $value, $context);
    }

    /**
     * @throws \Throwable in case of any internal error occurs
     */
    private function matchThroughLogger(LoggerInterface $logger, mixed $value, RuntimeContext $context): bool
    {
        $this->logBeforeMatch($logger, $value, $context);

        try {
            $result = parent::match($value, $context);
        } catch (\Throwable $e) {
            $this->logErrorMatch($logger, $value, $context, $e);

            throw $e;
        }

        $this->logAfterMatch($logger, $value, $context, $result);

        return $result;
    }

    private function logBeforeCast(LoggerInterface $logger, mixed $value, RuntimeContext $context): void
    {
        $logger->debug('[{group}] Casting "{value}" by {type_name} type', [
            ...$this->getLoggerArguments($value, $context, $this->cast),
        ]);
    }

    private function logAfterCast(LoggerInterface $logger, mixed $value, RuntimeContext $context, mixed $result): void
    {
        $logger->info('[{group}] Casted "{value}" to "{result}" by {type_name} type', [
            'result' => $result,
            ...$this->getLoggerArguments($value, $context, $this->cast),
        ]);
    }

    private function logErrorCast(LoggerInterface $logger, mixed $value, RuntimeContext $context, \Throwable $e): void
    {
        $logger->info('[{group}] Casting error: {message}', [
            'error' => $e->getMessage(),
            ...$this->getLoggerArguments($value, $context, $this->cast),
        ]);
    }

    public function cast(mixed $value, RuntimeContext $context): mixed
    {
        $logger = $context->config->findLogger();

        if ($logger === null) {
            return parent::cast($value, $context);
        }

        return $this->castThroughLogger($logger, $value, $context);
    }

    /**
     * @return TResult
     * @throws \Throwable in case of any internal error occurs
     */
    private function castThroughLogger(LoggerInterface $logger, mixed $value, RuntimeContext $context): mixed
    {
        $this->logBeforeCast($logger, $value, $context);

        try {
            $result = parent::cast($value, $context);
        } catch (\Throwable $e) {
            $this->logErrorCast($logger, $value, $context, $e);

            throw $e;
        }

        $this->logAfterCast($logger, $value, $context, $result);

        return $result;
    }
}
