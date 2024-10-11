<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @phpstan-consistent-constructor
 */
abstract class NamedType implements TypeInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        protected readonly string $name,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        return new NamedTypeNode($this->name);
    }
}
