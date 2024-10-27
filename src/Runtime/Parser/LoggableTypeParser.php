<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Parser;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeParser implements
    TypeParserInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly TypeParserInterface $delegate,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger;
    }

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $this->logger?->debug('Fetching an AST statement from the definition "{definition}"', [
            'definition' => $definition,
        ]);

        $statement = $this->delegate->getStatementByDefinition($definition);

        $this->logger?->info('Fetched an AST statement from the definition "{definition}"', [
            'definition' => $definition,
            'statement' => $statement,
        ]);

        return $statement;
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        $this->logger?->debug('Fetching an AST statement from the value "{value}"', [
            'value' => $value,
        ]);

        $statement = $this->delegate->getStatementByValue($value);

        $this->logger?->info('Fetched an AST statement from the value "{value}"', [
            'value' => $value,
            'statement' => $statement,
        ]);

        return $statement;
    }
}
