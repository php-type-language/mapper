<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Reader;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Reader\LazyMetadataReader;
use TypeLang\Mapper\Mapping\Reader\MetadataReaderInterface;
use TypeLang\Mapper\Mapping\Reader\NamingStrategyMetadataReader;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Mapper\Naming\SnakeCaseNamingStrategy;

final class LazyMetadataReaderTest extends TestCase
{
    public function testReaderIsCreatedOnFirstReadAndOnlyOnce(): void
    {
        $created = 0;

        $reader = new LazyMetadataReader(static function () use (&$created): MetadataReaderInterface {
            ++$created;

            return new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy());
        });

        self::assertSame(0, $created);

        $reader->read(new \ReflectionClass(\stdClass::class));
        $reader->read(new \ReflectionClass(\stdClass::class));

        self::assertSame(1, $created);
    }

    public function testReadIsDelegatedWithGivenMetadata(): void
    {
        $metadata = (new ClassDraft(\stdClass::class))
            ->withMember(MemberDraft::createForProperty('displayName'));

        $reader = new LazyMetadataReader(
            static fn(): MetadataReaderInterface => new NamingStrategyMetadataReader(new SnakeCaseNamingStrategy()),
        );

        $result = $reader->read(new \ReflectionClass(\stdClass::class), $metadata);

        self::assertSame('display_name', $result->findMember('displayName')?->read?->name);
    }
}
