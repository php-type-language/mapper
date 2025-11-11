<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\InvalidTemplateArgumentException;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;

#[Group('exception')]
#[CoversClass(InvalidTemplateArgumentException::class)]
final class InvalidTemplateArgumentTest extends DefinitionExceptionTestCase
{
    #[TestDox('expected int<string>, passed int<T>')]
    public function testInvalidArgumentGiven(): void
    {
        $this->expectException(InvalidTemplateArgumentException::class);
        $this->expectExceptionMessage('Passed template argument #2 of type int<T, U> must be of type string, but U given');

        $type = self::parse('int<T, U>');

        assert($type instanceof NamedTypeNode);

        $argument = $type->arguments?->last();
        assert($argument instanceof TemplateArgumentNode);

        throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
            argument: $argument,
            expected: new NamedTypeNode('string'),
            type: $type,
        );
    }

    #[TestDox('[undefined behaviour] all works if pass an non-node\'s argument')]
    public function testOfNonOwnArgument(): void
    {
        $this->expectException(InvalidTemplateArgumentException::class);
        $this->expectExceptionMessage('Passed template argument #0 of type another-type must be of type string, but T given');

        $type = self::parse('int<T>');
        assert($type instanceof NamedTypeNode);

        $argument = $type->arguments?->first();
        assert($argument instanceof TemplateArgumentNode);

        throw InvalidTemplateArgumentException::becauseTemplateArgumentMustBe(
            argument: $argument,
            expected: new NamedTypeNode('string'),
            type: self::parse('another-type'),
        );
    }
}
