<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Assert;

use PHPUnit\Framework\Assert;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Tests\Context\Context;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class ValueAssertions extends Context
{
    public function assertCompareWithCode(string $expected, mixed $actual, string $message = ''): void
    {
        if (\str_starts_with($expected, '<error:') && \str_ends_with($expected, '>')) {
            $expected = \trim(\substr($expected, 7, -1));

            Assert::assertInstanceOf(InvalidValueException::class, $actual);
            Assert::assertStringContainsString($expected, $actual->getMessage());

            return;
        }

        if ($actual instanceof \Throwable) {
            throw $actual;
        }

        $expectedValue = eval(\sprintf('return %s;', $expected));

        if (\is_float($expectedValue) && \is_nan($expectedValue)) {
            Assert::assertNan($actual, $message);
        } elseif (\is_object($expectedValue) || \is_object($actual)) {
            Assert::assertEquals($expectedValue, $actual, $message);
        } else {
            Assert::assertSame($expectedValue, $actual, $message);
        }
    }
}
