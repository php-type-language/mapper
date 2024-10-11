<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type;

use TypeLang\Mapper\Runtime\Context\LocalContext;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentsListNode;
use TypeLang\Parser\Node\Stmt\TypeStatement;

/**
 * @phpstan-consistent-constructor
 */
abstract class GenericType implements TypeInterface
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        protected readonly TypeInterface $type,
        protected readonly string $name,
    ) {}

    public function getTypeStatement(LocalContext $context): TypeStatement
    {
        if (!$context->isDetailedTypes()) {
            return new NamedTypeNode($this->name);
        }

        return new NamedTypeNode($this->name, new TemplateArgumentsListNode([
            new TemplateArgumentNode($this->type->getTypeStatement($context)),
        ]));
    }
}
