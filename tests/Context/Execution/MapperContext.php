<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Execution;

use Behat\Behat\Context\Exception\ContextNotFoundException;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\UnknownClassOrInterfaceException;
use TypeLang\Mapper\Runtime\Context\RootContext;
use TypeLang\Mapper\Runtime\Context as MappingContext;
use TypeLang\Mapper\Tests\Context\Context;
use TypeLang\Mapper\Tests\Context\Execution\MapperContext\ComparisonResult;
use TypeLang\Mapper\Tests\Context\Execution\MapperContext\ComparisonSet;
use TypeLang\Mapper\Tests\Context\Execution\MapperContext\FailedComparisonResult;
use TypeLang\Mapper\Tests\Context\Execution\MapperContext\ValueType;
use TypeLang\Mapper\Tests\Context\Provider\ConfigurationContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeParserContext;
use TypeLang\Mapper\Tests\Context\Provider\TypeRepositoryContext;
use TypeLang\Mapper\Tests\Extension\ContextArgumentTransformerExtension\AsTestingContext;

/**
 * @api
 * @see http://behat.org/en/latest/quick_start.html
 */
#[AsTestingContext('runtime')]
final class MapperContext extends Context
{
    private ?MappingContext $context = null;

    private ?ComparisonSet $result = null;

    /**
     * @api
     */
    public function getContext(): MappingContext
    {
        Assert::assertNotNull($this->context, 'Runtime Context has not been set');

        return $this->context;
    }

    /**
     * @api
     */
    public function setContext(MappingContext $context): MappingContext
    {
        return $this->context = $context;
    }

    /**
     * @api
     */
    public function getResult(): ComparisonSet
    {
        Assert::assertNotNull($this->result, 'Result set has not been set');

        return $this->result;
    }

    /**
     * @api
     */
    public function setResult(ComparisonSet $set): ComparisonSet
    {
        return $this->result = $set;
    }

    /**
     * @throws ContextNotFoundException
     */
    private function setNormalizationContext(mixed $value): MappingContext
    {
        return $this->setContext(RootContext::forNormalization(
            value: $value,
            config: $this->from(ConfigurationContext::class)
                ->getCurrent(),
            parser: $this->from(TypeParserContext::class)
                ->getFacade(),
            types: $this->from(TypeRepositoryContext::class)
                ->getFacade(),
        ));
    }

    /**
     * @throws ContextNotFoundException
     */
    private function setDenormalizationContext(mixed $value): MappingContext
    {
        return $this->setContext(RootContext::forDenormalization(
            value: $value,
            config: $this->from(ConfigurationContext::class)
                ->getCurrent(),
            parser: $this->from(TypeParserContext::class)
                ->getFacade(),
            types: $this->from(TypeRepositoryContext::class)
                ->getFacade(),
        ));
    }

    /**
     * @return iterable<ValueType, mixed>
     */
    private static function values(): iterable
    {
        foreach (ValueType::cases() as $case) {
            yield $case => match ($case) {
                ValueType::String => 'EXAMPLE',
                ValueType::Null => null,
                ValueType::Int => 0xDEAD_BEEF,
                ValueType::True => true,
                ValueType::False => false,
                ValueType::Float => 42.0,
                ValueType::Inf => \INF,
                ValueType::Nan => \NAN,
                ValueType::Object => new \stdClass(),
                ValueType::Array => [1, 2, 3],
            };
        }
    }

    private static function parse(string $value): mixed
    {
        return match ($value) {
            'inf' => \INF,
            '-inf' => -\INF,
            'nan' => \NAN,
            default => \json_decode($value, flags: \JSON_THROW_ON_ERROR),
        };
    }

    #[When('match when normalize')]
    public function whenMatchNormalize(): void
    {
        $results = $this->setResult(new ComparisonSet());

        $type = $this->from(TypeContext::class)
            ->getCurrent();

        foreach (self::values() as $case => $input) {
            $context = $this->setNormalizationContext($input);

            $output = $type->match($input, $context);

            $results->success($case, $input, $output);
        }
    }

    #[When('normalize')]
    #[When('cast when normalize')]
    public function whenNormalize(): void
    {
        $results = $this->setResult(new ComparisonSet());

        $type = $this->from(TypeContext::class)
            ->getCurrent();

        foreach (self::values() as $case => $input) {
            $context = $this->setNormalizationContext($input);

            try {
                $output = $type->cast($input, $context);
                $results->success($case, $input, $output);
            } catch (\Throwable $e) {
                $results->fail($case, $input, $e);
            }
        }
    }

    #[When('match when denormalize')]
    public function whenMatchDenormalize(): void
    {
        $results = $this->setResult(new ComparisonSet());

        $type = $this->from(TypeContext::class)
            ->getCurrent();

        foreach (self::values() as $case => $input) {
            $context = $this->setDenormalizationContext($input);

            $output = $type->match($input, $context);

            $results->success($case, $input, $output);
        }
    }

    #[When('denormalize')]
    #[When('cast when denormalize')]
    public function whenDenormalize(): void
    {
        $results = $this->setResult(new ComparisonSet());

        $type = $this->from(TypeContext::class)
            ->getCurrent();

        foreach (self::values() as $case => $input) {
            $context = $this->setDenormalizationContext($input);

            try {
                $output = $type->cast($input, $context);
                $results->success($case, $input, $output);
            } catch (\Throwable $e) {
                $results->fail($case, $input, $e);
            }
        }
    }

    /**
     * @param non-empty-string $name
     * @throws Exception
     * @throws ExpectationFailedException
     */
    #[Then('/^type "(?P<name>.+?)" must be successful$/')]
    public function thenTypeMustBeSuccessful(string $name): ComparisonResult
    {
        $results = $this->getResult();

        $result = $results->getByKey($name);

        Assert::assertNotInstanceOf(FailedComparisonResult::class, $result);

        return $result;
    }

    /**
     * @param non-empty-string $name
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws UnknownClassOrInterfaceException
     */
    #[Then('/^type "(?P<name>.+?)" must be failed/')]
    public function thenTypeMustBeFail(string $name): FailedComparisonResult
    {
        $results = $this->getResult();

        $result = $results->getByKey($name);

        Assert::assertInstanceOf(FailedComparisonResult::class, $result);

        return $result;
    }

    /**
     * @param non-empty-string $name
     * @throws Exception
     * @throws ExpectationFailedException
     */
    #[Then('/^type "(?P<name>.+?)" must be matched$/')]
    public function thenTypeMustBeMatched(string $name): void
    {
        $result = $this->thenTypeMustBeSuccessful($name);

        Assert::assertTrue($result->result);
    }

    /**
     * @param non-empty-string $name
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \JsonException
     */
    #[Then('/^type "(?P<name>.+?)" is (?P<value>.+?)$/')]
    public function thenTypeMustBe(string $name, string $value): void
    {
        $result = $this->thenTypeMustBeSuccessful($name);
        $expected = self::parse($value);

        if (\is_float($expected) && \is_nan($expected)) {
            Assert::assertNan($result->result);
        } else {
            Assert::assertSame($expected, $result->result);
        }
    }

    /**
     * @param non-empty-string $name
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \JsonException
     */
    #[Then('/^type "(?P<name>.+?)" is not (?P<value>.+?)$/')]
    public function thenTypeMustNotBe(string $name, string $value): void
    {
        $result = $this->thenTypeMustBeSuccessful($name);
        $expected = self::parse($value);

        if (\is_float($expected) && \is_nan($expected)) {
            Assert::assertFalse(\is_nan($result->result));
        } else {
            Assert::assertNotSame($expected, $result->result);
        }
    }

    /**
     * @param non-empty-string $name
     * @throws Exception
     * @throws ExpectationFailedException
     */
    #[Then('/^type "(?P<name>.+?)" must not be matched$/')]
    public function thenTypeMustNotBeMatched(string $name): void
    {
        $result = $this->thenTypeMustBeSuccessful($name);

        Assert::assertFalse($result->result);
    }

    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws ContextNotFoundException
     */
    #[Then('other types must be matched')]
    public function thenOtherMustBeMatched(): void
    {
        $results = $this->getResult();

        foreach ($results->getAllNonMatchedResults() as $index => $result) {
            Assert::assertTrue($result->result, \sprintf(
                'Comparison result "%s" must not be matched by the %s',
                $index,
                \get_debug_type($this->from(TypeContext::class)->getCurrent()),
            ));
        }
    }

    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws ContextNotFoundException
     */
    #[Then('other types must fail')]
    public function thenOtherMustFail(): void
    {
        $results = $this->getResult();

        foreach ($results->getAllNonMatchedResults() as $index => $result) {
            Assert::assertInstanceOf(FailedComparisonResult::class, $result, \sprintf(
                'Comparison result "%s" must fail by the %s',
                $index,
                \get_debug_type($this->from(TypeContext::class)->getCurrent()),
            ));
        }
    }

    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws ContextNotFoundException
     */
    #[Then('other types must not be matched')]
    public function thenOtherMustNotBeMatched(): void
    {
        $results = $this->getResult();

        foreach ($results->getAllNonMatchedResults() as $index => $result) {
            Assert::assertFalse($result->result, \sprintf(
                'Comparison result "%s" must not be matched by the %s',
                $index,
                \get_debug_type($this->from(TypeContext::class)->getCurrent()),
            ));
        }
    }

    /**
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws ContextNotFoundException
     */
    #[Then('other types must not fail')]
    public function thenOtherMustNotFail(): void
    {
        $results = $this->getResult();

        foreach ($results->getAllNonMatchedResults() as $index => $result) {
            Assert::assertNotInstanceOf(FailedComparisonResult::class, $result, \sprintf(
                'Comparison result "%s" must not fail by the %s: %s',
                $index,
                \get_debug_type($this->from(TypeContext::class)->getCurrent()),
                $result->result,
            ));
        }
    }
}
