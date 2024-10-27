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

    public function getStatementByDefinition(#[Language('PHP')] string $definition): TypeStatement
    {
        $this->logger->debug('Fetching an AST statement by the definition "{definition}"', [
            'definition' => $definition,
        ]);

        $statement = $this->delegate->getStatementByDefinition($definition);

        $this->logger->info('An AST statement was fetched by the definition "{definition}"', [
            'definition' => $definition,
            'statement' => $statement,
        ]);

        return $statement;
    }

    public function getStatementByValue(mixed $value): TypeStatement
    {
        $this->logger->debug('Fetching an AST statement by the value "{value}"', [
            'value' => $value,
        ]);

        $statement = $this->delegate->getStatementByValue($value);

        $this->logger->info('An AST statement was fetched by the value "{value}"', [
            'value' => $value,
            'statement' => $statement,
        ]);

        return $statement;
    }
}
