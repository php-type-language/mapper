<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Repository\LoggableTypeRepository\LoggableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeRepository implements TypeRepositoryInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeRepositoryInterface $delegate,
    ) {}

    public function getTypeByDefinition(#[Language('PHP')] string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger->debug('Fetching the type by the definition "{definition}"', [
            'definition' => $definition,
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByDefinition($definition, $context);

        $this->logger->info('Fetched the type {type} by the definition "{definition}"', [
            'definition' => $definition,
            'type' => $result::class . '#' . \spl_object_id($result),
            'context' => $context,
        ]);

        return new LoggableType($this->logger, $result);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger->debug('Fetching the type by the value "{value}"', [
            'value' => $value,
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByValue($value, $context);

        $this->logger->info('Fetched the type {type} by the value "{value}"', [
            'value' => $value,
            'type' => $result::class . '#' . \spl_object_id($result),
            'context' => $context,
        ]);

        return new LoggableType($this->logger, $result);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger->debug('Fetching the type by the AST statement "{statement}"', [
            'statement' => $statement,
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByStatement($statement, $context);

        $this->logger->info('Fetched the type {type} by the AST statement "{statement}"', [
            'statement' => $statement,
            'type' => $result::class . '#' . \spl_object_id($result),
            'context' => $context,
        ]);

        return new LoggableType($this->logger, $result);
    }
}
