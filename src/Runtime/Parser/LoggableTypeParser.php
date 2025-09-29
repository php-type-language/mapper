<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

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
    private function before(string $definition): void
    {
        $this->logger->debug('Fetching an AST by "{definition}"', [
            'definition' => $definition,
        ]);
    }

    /**
     * @param non-empty-string $definition
     */
    private function after(string $definition, TypeStatement $statement): void
    {
        $this->logger->info('AST was fetched by "{definition}"', [
            'definition' => $definition,
            'statement' => $statement,
        ]);
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $this->before($definition);

        $statement = $this->delegate->getStatementByDefinition($definition);

        $this->after($definition, $statement);

        return $statement;
    }
}
