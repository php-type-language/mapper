<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository;

use JetBrains\PhpStorm\Language;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Parser\Node\Stmt\TypeStatement;

abstract class TypeRepositoryDecorator implements
    TypeRepositoryInterface,
    InnerTypeRepositoryContainerInterface
{
    public function __construct(
        private readonly TypeRepositoryInterface $delegate,
    ) {
        $this->setInnerContext($this);
    }

    public function setInnerContext(TypeRepositoryInterface $inner): void
    {
        if (!$this->delegate instanceof InnerTypeRepositoryContainerInterface) {
            return;
        }

        $this->delegate->setInnerContext($this);
    }

    public function getTypeByDefinition(#[Language('PHP')] string $definition, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getTypeByDefinition($definition, $context);
    }

    public function getTypeByValue(mixed $value, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getTypeByValue($value, $context);
    }

    public function getTypeByStatement(TypeStatement $statement, ?\ReflectionClass $context = null): TypeInterface
    {
        return $this->delegate->getTypeByStatement($statement, $context);
    }
}
