<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

final class NamingStrategyMetadataReaderTest extends TestCase
{
    public function testStrategyNamesOnlyPropertiesWithoutExternalName(): void
    {
        $metadata = (new ClassDraft(\stdClass::class))
            ->withMember(MemberDraft::createForProperty('displayName'))
            ->withMember((MemberDraft::createForProperty('userId'))->withName('id'));

        $result = (new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy()))
            ->read(new \ReflectionClass(\stdClass::class), $metadata);

        self::assertSame('display_name', $result->findMember('displayName')?->read?->name);
        self::assertSame('id', $result->findMember('userId')?->read?->name);
    }

    public function testFirstReaderAddsNoProperties(): void
    {
        $result = self::readAsFirst(new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy()), \stdClass::class);

        self::assertSame(\stdClass::class, $result->name);
        self::assertSame([], $result->members);
    }
}
