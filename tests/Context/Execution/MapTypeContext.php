<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Execution;

use Behat\Gherkin\Node\TableNode;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use TypeLang\Mapper\Exception\Mapping\InvalidValueException;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Context\Provider\MappingContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
final class MapTypeContext extends Context
{
    private static function assertCompareWithCode(string $expectedCode, mixed $actualValue, string $message = ''): void
    {
        if (\str_starts_with($expectedCode, '<error:') && \str_ends_with($expectedCode, '>')) {
            $expectedCode = \trim(\substr($expectedCode, 7, -1));

            Assert::assertInstanceOf(InvalidValueException::class, $actualValue);
            Assert::assertStringContainsString($expectedCode, $actualValue->getMessage());

            return;
        }

        if ($actualValue instanceof \Throwable) {
            throw $actualValue;
        }

        $expectedValue = eval(\sprintf('return %s;', $expectedCode));

        if (\is_float($expectedValue) && \is_nan($expectedValue)) {
            Assert::assertNan($actualValue, $message);
        } elseif (\is_object($expectedValue)) {
            Assert::assertEquals($expectedValue, $actualValue, $message);
        } else {
            Assert::assertSame($expectedValue, $actualValue, $message);
        }
    }

    #[When('matching returns the following values:')]
    public function whenMatching(TableNode $table): void
    {
        $type = $this->from(TypeContext::class)
            ->getCurrent();

        foreach ($table->getRows() as [$inputCode, $expectedCode]) {
            $inputValue = eval('return ' . $inputCode . ';');
            $context = $this->from(MappingContext::class)
                ->setContextByValue($inputValue);

            $actualValue = $type->match($inputValue, $context);

            self::assertCompareWithCode(
                expectedCode: $expectedCode,
                actualValue: $actualValue,
                message: \vsprintf('Type %s expects %s to be %s, got %s', [
                    $type::class,
                    $inputCode,
                    $expectedCode,
                    $actualValue ? 'true' : 'false',
                ]),
            );
        }
    }

    #[When('casting returns the following values:')]
    public function whenCasting(TableNode $table): void
    {
        $type = $this->from(TypeContext::class)
            ->getCurrent();

        foreach ($table->getRows() as [$inputCode, $expectedCode]) {
            $inputValue = eval('return ' . $inputCode . ';');
            $context = $this->from(MappingContext::class)
                ->setContextByValue($inputValue);

            try {
                $actualValue = $type->cast($inputValue, $context);
            } catch (\Throwable $e) {
                self::assertCompareWithCode($expectedCode, $e);

                continue;
            }

            self::assertCompareWithCode(
                expectedCode: $expectedCode,
                actualValue: $actualValue,
                message: \vsprintf('Type %s expects %s to be %s, got %s', [
                    $type::class,
                    $inputCode,
                    $expectedCode,
                    \get_debug_type($actualValue),
                ]),
            );
        }
    }
}
