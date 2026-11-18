<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\ClassType;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Wraps the type of {@see AddressStub} in {@see OpaqueTypeStub}.
 */
final class AddressDecoratorStub implements TypeDecoratorInterface
{
    public function decorate(TypeInterface $type, TypeNode $node, BuildContext $context): TypeInterface
    {
        if (!$type instanceof ClassType || $type->metadata->name !== AddressStub::class) {
            return $type;
        }

        return new OpaqueTypeStub($type);
    }
}
