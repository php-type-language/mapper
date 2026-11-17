<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Instantiator\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase as BaseTestCase;

#[Group('unit'), Group('type-lang/instantiator')]
abstract class TestCase extends BaseTestCase
{
    /**
     * @param callable(): mixed $fn
     */
    protected static function captureThrowable(callable $fn): \Throwable
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            return $e;
        }

        self::fail('Expected a throwable to be thrown, but none was');
    }
}
