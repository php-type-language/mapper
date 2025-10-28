<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Context\Direction;
use TypeLang\Mapper\Context\RootContext;
use TypeLang\Mapper\Exception\Runtime\InvalidValueException;
use TypeLang\Mapper\Platform\PlatformInterface;
use TypeLang\Mapper\Platform\StandardPlatform;
use TypeLang\Mapper\Type\Extractor\NativeTypeExtractor;
use TypeLang\Mapper\Type\Extractor\TypeExtractorInterface;
use TypeLang\Mapper\Type\Parser\TypeLangParser;
use TypeLang\Mapper\Type\Parser\TypeParserInterface;
use TypeLang\Mapper\Type\Repository\TypeRepository;
use TypeLang\Mapper\Type\Repository\TypeRepositoryInterface;

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

    protected function createConfiguration(bool $strictTypes = true): Configuration
    {
        return new Configuration(
            strictTypes: $strictTypes,
        );
    }

    protected function createTypeExtractor(): TypeExtractorInterface
    {
        return new NativeTypeExtractor();
    }

    protected function createPlatform(): PlatformInterface
    {
        return new StandardPlatform();
    }

    protected function createTypeParser(): TypeParserInterface
    {
        return TypeLangParser::createFromPlatform($this->createPlatform());
    }

    protected function createTypeRepository(Direction $direction): TypeRepositoryInterface
    {
        $platform = $this->createPlatform();

        return new TypeRepository(
            parser: $this->createTypeParser(),
            builders: $platform->getTypes($direction),
        );
    }

    protected function createNormalizationContext(mixed $value, bool $strictTypes = true): RootContext
    {
        return RootContext::forNormalization(
            value: $value,
            config: $this->createConfiguration($strictTypes),
            extractor: $this->createTypeExtractor(),
            parser: $this->createTypeParser(),
            types: $this->createTypeRepository(Direction::Normalize),
        );
    }

    protected function createDenormalizationContext(mixed $value, bool $strictTypes = true): RootContext
    {
        return RootContext::forDenormalization(
            value: $value,
            config: $this->createConfiguration($strictTypes),
            extractor: $this->createTypeExtractor(),
            parser: $this->createTypeParser(),
            types: $this->createTypeRepository(Direction::Denormalize),
        );
    }

    protected function expectTypeErrorIfException(mixed $expected): void
    {
        if (!$expected instanceof \Throwable) {
            return;
        }

        $this->expectExceptionMessage($expected->getMessage());
        $this->expectException(InvalidValueException::class);
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
