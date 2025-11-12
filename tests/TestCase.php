<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;

abstract class TestCase extends BaseTestCase
{
    private static int $dataProviderIndex = 0;

    protected static function dataProviderOf(iterable $data): iterable
    {
        foreach ($data as $value => $expected) {
            yield self::dataProviderKeyOf($value) => [$value, $expected];
        }
    }

    /**
     * @return non-empty-string
     */
    private static function dataProviderKeyOf(mixed $value): string
    {
        return \vsprintf('%s(%s)#%d', [
            \get_debug_type($value),
            \is_array($value) || \is_object($value) ? \json_encode($value) : \var_export($value, true),
            ++self::$dataProviderIndex,
        ]);
    }

    protected function expectTypeErrorIfException(mixed $expected): void
    {
        if (!$expected instanceof \Throwable) {
            return;
        }

        $this->expectExceptionMessage($expected->getMessage());
    }

    protected static function assertIfNotException(mixed $expected, mixed $actual): void
    {
        switch (true) {
            case $expected instanceof \Throwable:
                break;
            case \is_array($expected):
            case \is_object($expected):
                self::assertEquals($expected, $actual);
                break;
            case \is_float($expected) && \is_nan($expected):
                self::assertNan($actual);
                break;
            default:
                self::assertSame($expected, $actual);
        }
    }
}
