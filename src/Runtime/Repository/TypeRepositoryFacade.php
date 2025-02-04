<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use TypeLang\Mapper\Runtime\Parser\TypeParserFacadeInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

final class TypeRepositoryFacade implements TypeRepositoryFacadeInterface
{
    public function __construct(
        private readonly TypeParserFacadeInterface $parser,
        private readonly TypeRepositoryInterface $runtime,
    ) {}

    public function getTypeByDefinition(string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parser->getStatementByDefinition($definition);

        return $this->runtime->getTypeByStatement($statement, $context);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        $statement = $this->parser->getStatementByValue($value);

        return $this->runtime->getTypeByStatement($statement, $context);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->runtime->getTypeByStatement($statement, $context);
    }
}
