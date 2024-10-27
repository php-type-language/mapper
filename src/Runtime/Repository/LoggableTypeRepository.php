<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeRepository implements
    TypeRepositoryInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly TypeRepositoryInterface $delegate,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger;
    }

    public function getTypeByDefinition(#[Language('PHP')] string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger?->debug('Fetching the type from the definition "{definition}"', [
            'definition' => $definition,
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByDefinition($definition, $context);

        $this->logger?->info('Fetched the type {type} from the definition "{definition}"', [
            'definition' => $definition,
            'type' => $result,
            'context' => $context,
        ]);

        return $result;
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger?->debug('Fetching the type from the value "{value}"', [
            'value' => $value,
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByValue($value, $context);

        $this->logger?->info('Fetched the type {type} from the value "{value}"', [
            'value' => $value,
            'type' => $result,
            'context' => $context,
        ]);

        return $result;
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger?->debug('Fetching the type from the AST statement "{statement}"', [
            'statement' => $statement,
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByStatement($statement, $context);

        $this->logger?->info('Fetched the type {type} from the AST statement "{statement}"', [
            'statement' => $statement,
            'type' => $result,
            'context' => $context,
        ]);

        return $result;
    }
}
