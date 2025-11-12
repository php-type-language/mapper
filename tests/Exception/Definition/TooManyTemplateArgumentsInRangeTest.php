<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Exception\Definition;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use TypeLang\Mapper\Exception\Definition\Template\TooManyTemplateArgumentsInRangeException;

#[Group('exception')]
#[CoversClass(TooManyTemplateArgumentsInRangeException::class)]
final class TooManyTemplateArgumentsInRangeTest extends DefinitionExceptionTestCase
{
    #[TestDox('supported int<T[, U]> (from 1 to 2 arguments), passed int<T, U, V> (3 arguments)')]
    public function testRangeOverflow(): void
    {
        $this->expectException(TooManyTemplateArgumentsInRangeException::class);
        $this->expectExceptionMessage('Type "int<T, U, V>" only accepts from 1 to 2 template argument(s), but 3 were passed');

        throw TooManyTemplateArgumentsInRangeException::becauseHasRedundantArgument(
            minArgumentsCount: 1,
            maxArgumentsCount: 2,
            type: self::parse('int<T, U, V>'),
        );
    }

    #[TestDox('[UB] supported int<T[, U]> (from 1 to 2 arguments), passed int<T> (1 argument)')]
    public function testRangeMaxUnderflow(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsInRangeException::becauseHasRedundantArgument(
            minArgumentsCount: 1,
            maxArgumentsCount: 2,
            type: self::parse('int<T>'),
        );
    }

    #[TestDox('[UB] supported int<T[, U]> (from 1 to 2 arguments), passed int (0 arguments)')]
    public function testRangeMinUnderflow(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsInRangeException::becauseHasRedundantArgument(
            minArgumentsCount: 1,
            maxArgumentsCount: 2,
            type: self::parse('int'),
        );
    }

    #[TestDox('[UB] supported int<T, U> (from 2 to 2 arguments), passed int<T, U> (2 arguments)')]
    public function testRangeEqual(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsInRangeException::becauseHasRedundantArgument(
            minArgumentsCount: 2,
            maxArgumentsCount: 2,
            type: self::parse('int<T, U>'),
        );
    }

    #[TestDox('[UB] supported int<T, U> (from 2 to 1 arguments), passed int<T, U> (2 arguments)')]
    public function testRangeInverse(): void
    {
        $this->skipIfAssertionsDisabled();
        $this->expectExceptionMessage('Semantic Violation');

        throw TooManyTemplateArgumentsInRangeException::becauseHasRedundantArgument(
            minArgumentsCount: 2,
            maxArgumentsCount: 1,
            type: self::parse('int<T, U>'),
        );
    }
}
