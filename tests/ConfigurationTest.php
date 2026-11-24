<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use TypeLang\Mapper\Configuration;

final class ConfigurationTest extends TestCase
{
    /**
     * @return iterable<non-empty-string, array{\Closure(Configuration): Configuration, non-empty-string, mixed}>
     */
    public static function settingDataProvider(): iterable
    {
        yield 'strict' => [static fn(Configuration $c): Configuration => $c->withStrict(true), 'strict', true];
        yield 'normalize as array' => [
            static fn(Configuration $c): Configuration => $c->withNormalizeAsArray(false),
            'normalizeAsArray',
            false,
        ];
        yield 'max violations' => [
            static fn(Configuration $c): Configuration => $c->withMaxViolations(100),
            'maxViolations',
            100,
        ];
        yield 'reject extra fields' => [
            static fn(Configuration $c): Configuration => $c->withRejectExtraFields(true),
            'rejectExtraFields',
            true,
        ];
        yield 'expected type depth' => [
            static fn(Configuration $c): Configuration => $c->withExpectedTypeDepth(3),
            'expectedTypeDepth',
            3,
        ];
        yield 'expected type max fields' => [
            static fn(Configuration $c): Configuration => $c->withExpectedTypeMaxFields(5),
            'expectedTypeMaxFields',
            5,
        ];
        yield 'max depth' => [
            static fn(Configuration $c): Configuration => $c->withMaxDepth(64),
            'maxDepth',
            64,
        ];
    }

    /**
     * @param \Closure(Configuration): Configuration $change
     * @param non-empty-string $setting
     */
    #[DataProvider('settingDataProvider')]
    public function testSettingIsChangedInCopyKeepingOthers(\Closure $change, string $setting, mixed $value): void
    {
        $config = new Configuration();
        $changed = $change($config);

        $expected = \get_object_vars($config);
        $expected[$setting] = $value;

        self::assertNotSame($config, $changed);
        self::assertSame($expected, \get_object_vars($changed));
        self::assertNotSame($value, \get_object_vars($config)[$setting]);
    }
}
