<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\PropertyMetadata;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\TestCase;

final class ClassAccessorsTest extends TestCase
{
    public function testReturnsOneAccessorForOneMetadataObject(): void
    {
        $accessors = new ClassAccessors();
        $metadata = new ClassMetadata(AddressStub::class);

        self::assertSame($accessors->get($metadata), $accessors->get($metadata));
    }

    public function testReturnsOwnAccessorForChangedMetadataOfSameClass(): void
    {
        $accessors = new ClassAccessors();
        $metadata = new ClassMetadata(AddressStub::class);
        $changed = $metadata->withProperty(new PropertyMetadata('city'));

        $accessor = $accessors->get($changed);

        self::assertNotSame($accessors->get($metadata), $accessor);
        self::assertSame([], $accessors->get($metadata)->writers);
        self::assertSame(['city'], \array_keys($accessor->writers));
    }
}
