<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Assert;

use Behat\Step\Then;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Context\Provider\MappingContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class TypeCastAssertions extends Context
{
    #[Then('/^cast of "(?P<input>.+?)" must return (?P<expected>.+?)$/')]
    public function thenCast(string $inputCode, string $expectedCode): void
    {
        $inputValue = eval(\sprintf('return %s;', $inputCode));

        $type = $this->from(TypeContext::class)
            ->getCurrent();

        $context = $this->from(MappingContext::class)
            ->setContextByValue($inputValue);

        $values = $this->from(ValueAssertions::class);

        try {
            $actualValue = $type->cast($inputValue, $context);

            $values->assertCompareWithCode(
                expected: $expectedCode,
                actual: $actualValue,
                message: \vsprintf('Type %s expects "%s" to be "%s" after casting:', [
                    $type::class,
                    $inputCode,
                    $expectedCode,
                ]),
            );
        } catch (\Throwable $e) {
            $values->assertCompareWithCode(
                expected: $expectedCode,
                actual: $e,
            );
        }
    }
}
