<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Parser;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeParser implements TypeParserInterface
{
    /**
     * @var non-empty-string
     */
    public const DEFAULT_PARSER_GROUP_NAME = 'PARSE';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeParserInterface $delegate,
        /**
         * @var non-empty-string
         */
        private readonly string $group = self::DEFAULT_PARSER_GROUP_NAME,
    ) {}

    /**
     * @param non-empty-string $definition
     */
    private function logBefore(string $definition): void
    {
        $this->logger->debug('[{group}] Parsing "{definition}" definition', [
            'group' => $this->group,
            'definition' => $definition,
        ]);
    }

    /**
     * @param non-empty-string $definition
     */
    private function logAfter(string $definition, TypeStatement $statement): void
    {
        $this->logger->info('[{group}] Parsed "{definition}" definition', [
            'group' => $this->group,
            'definition' => $definition,
            'statement' => $statement,
        ]);
    }

    private function logError(string $definition, \Throwable $e): void
    {
        $this->logger->error('[{group}] Parsing error: {error}', [
            'group' => $this->group,
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
