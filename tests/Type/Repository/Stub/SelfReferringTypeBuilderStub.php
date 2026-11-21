<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Repository\Stub;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Returns the type of its own node from the repository.
 *
 * @template-implements TypeBuilderInterface<mixed>
 */
final class SelfReferringTypeBuilderStub implements TypeBuilderInterface
{
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if (!$node instanceof NamedTypeNode || $node->name->toString() !== 'self-referring') {
            return null;
        }

        return $context->types->getType($node);
    }
}
