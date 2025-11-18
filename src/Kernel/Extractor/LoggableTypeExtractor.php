<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Extractor;

use Psr\Log\LoggerInterface;

final class LoggableTypeExtractor implements TypeExtractorInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_EXTRACTOR_GROUP_NAME = 'EXTRACT';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeExtractorInterface $delegate,
        /**
         * @var non-empty-string
         */
        private readonly string $group = self::DEFAULT_EXTRACTOR_GROUP_NAME,
    ) {}

    private function logBefore(mixed $value): void
    {
        $this->logger->debug('[{group}] Type inference for "{{value}}" value', [
            'group' => $this->group,
            'value' => $value,
        ]);
    }

    private function logAfter(mixed $value, string $definition): void
    {
        $this->logger->info('[{group}] Type inferred as "{{definition}}" for "{{value}}" value', [
            'group' => $this->group,
            'definition' => $definition,
            'value' => $value,
        ]);
    }

    private function logError(mixed $value, \Throwable $e): void
    {
        $this->logger->error('[{group}] Type inferring error: {error}', [
            'group' => $this->group,
            'value' => $value,
            'error' => $e->getMessage(),
        ]);
    }

    public function getDefinitionByValue(mixed $value): string
    {
        $this->logBefore($value);

        try {
            $result = $this->delegate->getDefinitionByValue($value);
        } catch (\Throwable $e) {
            $this->logError($value, $e);

            throw $e;
        }

        $this->logAfter($value, $result);

        return $result;
    }
}
