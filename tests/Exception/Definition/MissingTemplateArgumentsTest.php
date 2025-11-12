<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\MissingTemplateArgumentsException;

#[Group('exception')]
#[CoversClass(MissingTemplateArgumentsException::class)]
final class MissingTemplateArgumentsTest extends DefinitionExceptionTestCase
{
    #[TestDox('expected int<T, U, V> (3 arguments), passed int<T, U> (2 arguments)')]
    public function testManyArgumentsPassedWithGenericType(): void
    {
        $this->expectException(MissingTemplateArgumentsException::class);
        $this->expectExceptionMessage('Type "int<min, max>" expects at least 3 template argument(s), but 2 were passed');

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired(
            minArgumentsCount: 3,
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('expected int<T, U, V> (3 arguments), passed int (0 arguments)')]
    public function testNoArgumentsPassedWithSimpleType(): void
    {
        $this->expectException(MissingTemplateArgumentsException::class);
        $this->expectExceptionMessage('Type "int" expects at least 3 template argument(s), but 0 were passed');

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired(
            minArgumentsCount: 3,
            type: self::parse('int'),
        );
    }

    #[TestDox('[UB] expected int<T> (1 argument), passed int<T, U> (2 arguments)')]
    public function testManyArgumentsPassedWithInvalidGenericType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired(
            minArgumentsCount: 1,
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('[UB] expected int (0 argument), passed int (0 arguments)')]
    public function testNoArgumentsPassedWithInvalidType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired(
            minArgumentsCount: 0,
            type: self::parse('int'),
        );
    }

    #[TestDox('[UB] expected int (0 argument), passed int<T, U> (2 arguments)')]
    public function testNoArgumentsPassedWithInvalidGenericType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired(
            minArgumentsCount: 0,
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('[UB] expected int<T, U> (2 arguments), passed int<T, U> (2 arguments)')]
    public function testInvalidGenericTypeWithSameArgumentsCount(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw MissingTemplateArgumentsException::becauseArgumentsCountRequired(
            minArgumentsCount: 2,
            type: self::parse('int<min, max>'),
        );
    }
}
