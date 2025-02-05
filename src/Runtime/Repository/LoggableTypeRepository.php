<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use Psr\Log\LoggerInterface;
use TypeLang\Mapper\Platform\Standard\Type\TypeInterface;
use TypeLang\Mapper\Runtime\Repository\TypeDecorator\LoggableType;
use TypeLang\Mapper\Runtime\Repository\TypeDecorator\TypeDecoratorInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class LoggableTypeRepository extends TypeRepositoryDecorator
{
    public function __construct(
        private readonly LoggerInterface $logger,
        TypeRepositoryInterface $delegate,
    ) {
        parent::__construct($delegate);
    }

    #[\Override]
    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        $this->logger->debug('Fetching the type by the AST statement {statement_name}', [
            'statement' => $statement,
            'statement_name' => $statement::class . '#' . \spl_object_id($statement),
            'context' => $context,
        ]);

        $type = $result = parent::getTypeByStatement($statement, $context);

        if ($type instanceof TypeDecoratorInterface) {
            $type = $type->getDecoratedType();
        }

        $this->logger->info('The {type_name} was fetched by the AST statement {statement_name}', [
            'statement' => $statement,
            'statement_name' => $statement::class . '#' . \spl_object_id($statement),
            'type' => $type,
            'type_name' => $type::class . '#' . \spl_object_id($type),
            'context' => $context,
        ]);

        if ($result instanceof LoggableType) {
            return $result;
        }

        return new LoggableType($this->logger, $result);
    }
}
