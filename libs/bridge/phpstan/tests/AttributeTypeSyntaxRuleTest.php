<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\PHPStan\Tests;

use PHPStan\Analyser\Error;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;
use TypeLang\Mapper\Bridge\PHPStan\AttributeTypeSyntaxRule;
use TypeLang\Mapper\Bridge\PHPStan\TypeSyntaxChecker;

/**
 * @extends RuleTestCase<AttributeTypeSyntaxRule>
 */
#[Group('unit'), Group('type-lang/mapper')]
final class AttributeTypeSyntaxRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new AttributeTypeSyntaxRule();
    }

    public function testReportsTypesWrittenWrongInMappingAttributes(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/attributes.php']);

        self::assertSame([15, 18], \array_map(static fn(Error $error): ?int => $error->getLine(), $errors));

        foreach ($errors as $error) {
            self::assertSame(TypeSyntaxChecker::ERROR_IDENTIFIER, $error->getIdentifier());
        }
    }
}
