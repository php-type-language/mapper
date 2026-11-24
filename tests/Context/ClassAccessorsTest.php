<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Context;

use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Instantiator\InstantiatorFactory;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Struct\InputStructMetadata;
use TypeLang\Mapper\PropertyAccessor\PropertyAccessorFactory;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\TestCase;

final class ClassAccessorsTest extends TestCase
{
    public function testReturnsOneAccessorForOneMetadataObject(): void
    {
        $accessors = self::createAccessors();
        $metadata = new ClassMetadata(AddressStub::class);

        self::assertSame($accessors->get($metadata), $accessors->get($metadata));
    }

    public function testReturnsOwnAccessorForChangedMetadataOfSameClass(): void
    {
        $accessors = self::createAccessors();
        $metadata = new ClassMetadata(AddressStub::class);
        $changed = new ClassMetadata(
            name: AddressStub::class,
            input: new InputStructMetadata([new InputFieldMetadata('city', new SlotAccessMetadata('city'))]),
        );

        $accessor = $accessors->get($changed);

        self::assertNotSame($accessors->get($metadata), $accessor);
        self::assertSame([], $accessors->get($metadata)->writers);
        self::assertCount(1, $accessor->writers);
    }

    private static function createAccessors(): ClassAccessors
    {
        return new ClassAccessors(
            properties: PropertyAccessorFactory::createDefault(),
            instantiators: InstantiatorFactory::createDefault(),
            hooks: [],
        );
    }
}
