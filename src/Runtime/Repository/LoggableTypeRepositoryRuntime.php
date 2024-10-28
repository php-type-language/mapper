<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Runtime\Repository\LoggableTypeRepository\LoggableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeRepositoryRuntime implements TypeRepositoryRuntimeInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TypeRepositoryRuntimeInterface $delegate,
    ) {}

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger->debug('Fetching the type by the AST statement {statement_name}', [
            'statement' => $statement,
            'statement_name' => $statement::class . '#' . \spl_object_id($statement),
            'context' => $context,
        ]);

        $result = $this->delegate->getTypeByStatement($statement, $context);

        $this->logger->info('The {type_name} was fetched by the AST statement {statement_name}', [
            'statement' => $statement,
            'statement_name' => $statement::class . '#' . \spl_object_id($statement),
            'type' => $result,
            'type_name' => $result::class . '#' . \spl_object_id($result),
            'context' => $context,
        ]);

        if ($result instanceof LoggableType) {
            return $result;
        }

        return new LoggableType($this->logger, $result);
    }
}
