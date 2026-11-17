<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use TypeLang\Mapper\Context\ClassTypes;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\TestCase;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;
use TypeLang\Mapper\Type\StringType;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

final class ClassTypesTest extends TestCase
{
    public function testAsksRepositoryOncePerClassByItsFullName(): void
    {
        $repository = new class implements TypeRepositoryInterface {
            /**
             * @var list<string>
             */
            public array $requested = [];

            public function getType(TypeNode $node): TypeInterface
            {
                TestCase::assertInstanceOf(NamedTypeNode::class, $node);

                $this->requested[] = $node->name->toString();

                return new StringType();
            }
        };

        $types = new ClassTypes($repository);

        self::assertSame($types->get(AddressStub::class), $types->get(AddressStub::class));
        self::assertSame($types->get(\ArrayObject::class), $types->get(\ArrayObject::class));
        self::assertSame([AddressStub::class, 'ArrayObject'], $repository->requested);
    }
}
