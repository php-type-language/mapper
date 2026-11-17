<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Stub;

use TypeLang\Mapper\Bridge\Symfony\Attribute\AsMapperDecorator;
use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

#[AsMapperDecorator]
final class DecoratorStub implements TypeDecoratorInterface
{
    public function decorate(TypeInterface $type, TypeNode $node, BuildContext $context): TypeInterface
    {
        return $type;
    }
}
