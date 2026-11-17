<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan\Tests;

use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Bridge\PHPStan\MethodCallTypeSyntaxRule;
use TypeLang\Mapper\Bridge\PHPStan\TypeSyntaxChecker;

/**
 * @extends RuleTestCase<MethodCallTypeSyntaxRule>
 */
#[Group('unit'), Group('type-lang/mapper')]
final class MethodCallTypeSyntaxRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new MethodCallTypeSyntaxRule();
    }

    public function testReportsTypesWrittenWrongInCallsOfMapper(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/method-calls.php']);

        self::assertSame([13, 14, 16, 18], \array_map(static fn(Error $error): ?int => $error->getLine(), $errors));

        foreach ($errors as $error) {
            self::assertSame(TypeSyntaxChecker::ERROR_IDENTIFIER, $error->getIdentifier());
        }

        self::assertStringStartsWith('Type "list<int" is written wrong: ', $errors[0]->getMessage());
    }
}
