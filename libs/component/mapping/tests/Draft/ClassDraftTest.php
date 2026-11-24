<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Draft;

use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Tests\Stub\StatusStub;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class ClassDraftTest extends TestCase
{
    public function testPropertyOfSameNameIsReplacedInPlace(): void
    {
        $replacement = (MemberDraft::createForProperty('a'))->withName('first');

        $metadata = (new ClassDraft(\stdClass::class))
            ->withMember(MemberDraft::createForProperty('a'))
            ->withMember(MemberDraft::createForProperty('b'))
            ->withMember($replacement);

        self::assertSame(['a', 'b'], \array_keys($metadata->members));
        self::assertSame($replacement, $metadata->findMember('a'));
        self::assertNull($metadata->findMember('c'));
    }

    public function testWitherDoesNotChangeOriginal(): void
    {
        $metadata = new ClassDraft(\stdClass::class);

        $metadata->withMember(MemberDraft::createForProperty('a'));

        self::assertSame([], $metadata->members);
    }

    public function testMetadataHasFieldsOfDirectionsInOrderOfProperties(): void
    {
        $draft = (new ClassDraft(\stdClass::class))
            ->withMember((MemberDraft::createForProperty('a'))->withReadAccess(null))
            ->withMember(MemberDraft::createForProperty('b'))
            ->withMember((MemberDraft::createForProperty('c'))->withWriteAccess(null));

        $metadata = $draft->toMetadata();

        self::assertSame(\stdClass::class, $metadata->name);
        self::assertSame(['a', 'b'], self::getNames($metadata->input->fields));
        self::assertSame(['b', 'c'], self::getNames($metadata->output->fields));
    }

    public function testMetadataTakesOptionsOfDirections(): void
    {
        $type = TypeMetadata::createFromType(new NamedTypeNode(new Name('int')), new SourceInfo());
        $discriminator = new DiscriminatorMetadata('type', ['a' => $type], new SourceInfo());

        $metadata = (new ClassDraft(\stdClass::class, discriminator: $discriminator))
            ->withTypeErrorMessage('type')
            ->withNormalizeAsArray(true)
            ->toMetadata();

        self::assertSame($discriminator, $metadata->discriminator);
        self::assertSame('type', $metadata->input->typeErrorMessage);
        self::assertTrue($metadata->output->normalizeAsArray);
    }

    public function testEnumMetadataTakesAliasesOfCases(): void
    {
        $metadata = (new ClassDraft(StatusStub::class))
            ->withAlias('on', 'active')
            ->toEnumMetadata();

        self::assertSame(StatusStub::class, $metadata->name);
        self::assertSame(['on' => 'active'], $metadata->aliases);
    }
}
