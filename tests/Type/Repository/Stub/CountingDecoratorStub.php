<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type\Repository\Stub;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\NullableType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Wraps every named type into a nullable one and records their names.
 */
final class CountingDecoratorStub implements TypeDecoratorInterface
{
    /**
     * @var list<string>
     */
    public array $decorated = [];

    public function decorate(TypeInterface $type, TypeNode $node, BuildContext $context): TypeInterface
    {
        if ($node instanceof NamedTypeNode) {
            $this->decorated[] = $node->name->toString();
        }

        /** @phpstan-ignore-next-line return.type (the stub widens the value by null) */
        return new NullableType($type);
    }
}
