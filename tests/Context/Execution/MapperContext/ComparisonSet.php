<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context\Execution\MapperContext;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

/**
 * @template-implements \ArrayAccess<non-empty-lowercase-string, ComparisonResult>
 */
final class ComparisonSet implements \ArrayAccess
{
    /**
     * @var array<non-empty-lowercase-string, ComparisonResult>
     */
    private array $results = [];

    /**
     * @var array<non-empty-lowercase-string, ComparisonResult>
     */
    private array $nonMatchedResults = [];

    public function success(ValueType $type, mixed $input, mixed $output): void
    {
        $this->results[\strtolower($type->name)]
            = $this->nonMatchedResults[\strtolower($type->name)]
            = new ComparisonResult($input, $output);
    }

    public function fail(ValueType $type, mixed $input, \Throwable $failure): void
    {
        $this->results[\strtolower($type->name)]
            = $this->nonMatchedResults[\strtolower($type->name)]
            = new FailedComparisonResult($input, $failure);
    }

    /**
     * @param non-empty-string $type
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function getByKey(string $type): ComparisonResult
    {
        $index = \strtolower($type);

        Assert::assertArrayHasKey($index, $this->results);

        unset($this->nonMatchedResults[$index]);

        return $this->results[$index];
    }

    /**
     * @return iterable<non-empty-lowercase-string, ComparisonResult>
     */
    public function getAllNonMatchedResults(): iterable
    {
        yield from $this->nonMatchedResults;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists(\strtolower($offset), $this->results);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->results[\strtolower($offset)] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->results[\strtolower($offset)] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->results[\strtolower($offset)]);
    }
}
