<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Assert;

use Behat\Step\Then;
use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Context\Provider\MappingContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class TypeMatchAssertions extends Context
{
    #[Then('/^match of "(?P<value>.+?)" must return (?P<status>false|true)$/')]
    public function thenMatch(string $code, string $status): void
    {
        $value = eval(\sprintf('return %s;', $code));

        $type = $this->from(TypeContext::class)
            ->getCurrent();

        $context = $this->from(MappingContext::class)
            ->setContextByValue($value);

        $actual = $type->match($value, $context);

        $message = \vsprintf('Type %s expects matching of %s to be %s, got %s', [
            $type::class,
            $code,
            $status,
            $actual ? 'true' : 'false',
        ]);

        if (\strtolower($status) === 'true') {
            Assert::assertTrue($actual, $message);
        } else {
            Assert::assertFalse($actual, $message);
        }
    }
}
