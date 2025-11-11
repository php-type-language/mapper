<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\Hint\TemplateArgumentHintsNotSupportedException;
use TypeLang\Mapper\Type\Parser\TypeLangParser;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;
use TypeLang\Parser\Node\Stmt\Template\TemplateArgumentNode;

#[Group('exception')]
#[CoversClass(TemplateArgumentHintsNotSupportedException::class)]
final class TemplateArgumentHintsNotSupportedTest extends DefinitionExceptionTestCase
{
    #[TestDox('expected int<T> (0 hints), passed int<out T> (1 hint)')]
    public function testManyArgumentsPassedWithGenericType(): void
    {
        $this->expectException(TemplateArgumentHintsNotSupportedException::class);
        $this->expectExceptionMessage('Template argument #1 (T) of "int<out T>" does not support any hints, but "out" were passed');

        $type = self::parse('int<out T>');

        assert($type instanceof NamedTypeNode);
        assert($type->arguments->first() instanceof TemplateArgumentNode);

        throw TemplateArgumentHintsNotSupportedException::becauseTooManyHints(
            argument: $type->arguments->first(),
            type: $type,
        );
    }

    #[TestDox('[not applicable] expected int (0 arguments), passed int (0 arguments)')]
    public function testManyArgumentsPassedWithBasicType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Incorrect exception usage');

        $type = self::parse('int<T>');

        assert($type instanceof NamedTypeNode);
        assert($type->arguments->first() instanceof TemplateArgumentNode);

        throw TemplateArgumentHintsNotSupportedException::becauseTooManyHints(
            argument: $type->arguments->first(),
            type: $type,
        );
    }
}
