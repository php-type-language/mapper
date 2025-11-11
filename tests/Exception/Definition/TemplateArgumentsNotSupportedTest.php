<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\TemplateArgumentsNotSupportedException;

#[Group('exception')]
#[CoversClass(TemplateArgumentsNotSupportedException::class)]
final class TemplateArgumentsNotSupportedTest extends DefinitionExceptionTestCase
{
    #[TestDox('expected int (0 arguments), passed int<T, U> (2 arguments)')]
    public function testManyArgumentsPassedWithGenericType(): void
    {
        $this->expectException(TemplateArgumentsNotSupportedException::class);
        $this->expectExceptionMessage('Type "int<min, max>" does not support template arguments, but 2 were passed');

        throw TemplateArgumentsNotSupportedException::becauseTooManyArguments(
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('[not applicable] expected int (0 arguments), passed int (0 arguments)')]
    public function testManyArgumentsPassedWithBasicType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Incorrect exception usage');

        throw TemplateArgumentsNotSupportedException::becauseTooManyArguments(
            type: self::parse('int'),
        );
    }
}
