<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Extractor;

use Psr\Log\LoggerInterface;

final class LoggableTypeExtractor implements TypeExtractorInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeExtractorInterface $delegate,
    ) {}

    private function logBefore(mixed $value): void
    {
        $this->logger->debug('[EXTRACT] Type inference for "{{value}}" value', [
            'value' => $value,
        ]);
    }

    private function logAfter(mixed $value, string $definition): void
    {
        $this->logger->info('[EXTRACT] Type inferred as "{{definition}}" for "{{value}}" value', [
            'definition' => $definition,
            'value' => $value,
        ]);
    }

    private function logError(mixed $value, \Throwable $e): void
    {
        $this->logger->error('[EXTRACT] Type inferring error: {error}', [
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
