<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Kernel\Parser;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeParserInterface $delegate,
    ) {}

    /**
     * @param non-empty-string $definition
     */
    private function logBefore(string $definition): void
    {
        $this->logger->debug('[PARSE] Parsing "{definition}" definition', [
            'definition' => $definition,
        ]);
    }

    /**
     * @param non-empty-string $definition
     */
    private function logAfter(string $definition, TypeStatement $statement): void
    {
        $this->logger->info('[PARSE] Parsed "{definition}" definition', [
            'definition' => $definition,
            'statement' => $statement,
        ]);
    }

    private function logError(string $definition, \Throwable $e): void
    {
        $this->logger->error('[PARSE] Parsing error: {error}', [
            'definition' => $definition,
            'error' => $e->getMessage(),
        ]);
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $this->logBefore($definition);

        try {
            $statement = $this->delegate->getStatementByDefinition($definition);
        } catch (\Throwable $e) {
            $this->logError($definition, $e);

            throw $e;
        }

        $this->logAfter($definition, $statement);

        return $statement;
    }
}
