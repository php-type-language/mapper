<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsException;

#[Group('exception')]
#[CoversClass(TooManyTemplateArgumentsException::class)]
final class TooManyTemplateArgumentsTest extends DefinitionExceptionTestCase
{
    #[TestDox('supported int<T> (1 argument), passed int<T, U> (2 arguments)')]
    public function testWithOneArgument(): void
    {
        $this->expectException(TooManyTemplateArgumentsException::class);
        $this->expectExceptionMessage('Type "int<min, max>" only accepts 1 template argument(s), but 2 were passed');

        throw TooManyTemplateArgumentsException::becauseHasRedundantArgument(
            maxArgumentsCount: 1,
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('supported int (0 arguments), passed int<T, U> (2 arguments)')]
    public function testWithNoArguments(): void
    {
        $this->expectException(TooManyTemplateArgumentsException::class);
        $this->expectExceptionMessage('Type "int<min, max>" only accepts 0 template argument(s), but 2 were passed');

        throw TooManyTemplateArgumentsException::becauseHasRedundantArgument(
            maxArgumentsCount: 0,
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('[UB] supported int (0 arguments), passed int (0 arguments)')]
    public function testZeroArgsWithInvalidType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsException::becauseHasRedundantArgument(
            maxArgumentsCount: 0,
            type: self::parse('int'),
        );
    }

    #[TestDox('[UB] supported int<T, U, V> (3 arguments), passed int<T, U> (2 arguments)')]
    public function testWithInvalidGenericType(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsException::becauseHasRedundantArgument(
            maxArgumentsCount: 3,
            type: self::parse('int<min, max>'),
        );
    }

    #[TestDox('[UB] supported int<T, U> (2 arguments), passed int<T, U> (2 arguments)')]
    public function testInvalidGenericTypeWithSameArgumentsCount(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsException::becauseHasRedundantArgument(
            maxArgumentsCount: 2,
            type: self::parse('int<min, max>'),
        );
    }
}
