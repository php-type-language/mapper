<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

final class NamingStrategyMetadataReaderTest extends TestCase
{
    public function testStrategyNamesOnlyPropertiesWithoutExternalName(): void
    {
        $metadata = (new ClassMetadata(\stdClass::class))
            ->withProperty(new PropertyMetadata('displayName'))
            ->withProperty((new PropertyMetadata('userId'))->withExternalName('id'));

        $result = (new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy()))
            ->read(new \ReflectionClass(\stdClass::class), $metadata);

        self::assertSame('display_name', $result->findProperty('displayName')?->externalName);
        self::assertSame('id', $result->findProperty('userId')?->externalName);
    }

    public function testFirstReaderAddsNoProperties(): void
    {
        $result = self::readAsFirst(new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy()), \stdClass::class);

        self::assertSame(\stdClass::class, $result->name);
        self::assertSame([], $result->properties);
    }
}
