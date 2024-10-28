<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeParserRuntime implements TypeParserRuntimeInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeParserRuntimeInterface $delegate,
    ) {}

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
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
