<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use Psr\Log\LoggerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeParser implements TypeParserInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeParserInterface $delegate,
    ) {}

    public function getStatementByDefinition(string $definition): TypeStatement
    {
        $this->logger->debug('Fetching an AST by "{definition}"', [
            'definition' => $definition,
        ]);

        $statement = $this->delegate->getStatementByDefinition($definition);

        $this->logger->info('AST was fetched by "{definition}"', [
            'definition' => $definition,
            'statement' => $statement,
        ]);

        return $statement;
    }
}
