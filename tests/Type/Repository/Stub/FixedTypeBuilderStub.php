<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Repository\Stub;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * @template T
 *
 * @template-implements TypeBuilderInterface<T>
 */
final class FixedTypeBuilderStub implements TypeBuilderInterface
{
    /**
     * Context of the last build
     */
    public ?BuildContext $context = null;

    public function __construct(
        /**
         * @var non-empty-string
         */
        private readonly string $name,
        /**
         * @var TypeInterface<T>
         */
        private readonly TypeInterface $type,
    ) {}

    /**
     * @return TypeInterface<T>|null
     */
    public function tryBuild(TypeNode $node, BuildContext $context): ?TypeInterface
    {
        if (!$node instanceof NamedTypeNode || $node->name->toString() !== $this->name) {
            return null;
        }

        $this->context = $context;

        return $this->type;
    }
}
