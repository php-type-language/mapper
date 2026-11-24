<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Draft;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class MemberDraftTest extends TestCase
{
    public function testFieldsOfPropertyHaveItsNameUnlessGiven(): void
    {
        $property = MemberDraft::createForProperty('userId');

        self::assertSame('userId', $property->key);
        self::assertSame('userId', $property->read?->name);
        self::assertFalse($property->read->isNameExplicit);

        $renamed = $property->withName('user_id');

        self::assertSame('user_id', $renamed->read?->name);
        self::assertSame('user_id', $renamed->write?->name);
        self::assertTrue($renamed->write->isNameExplicit);
    }

    public function testBothDirectionsOfPropertyAccessStorageWithoutType(): void
    {
        $property = MemberDraft::createForProperty('userId');

        self::assertEquals(new AccessDraft(new SlotAccessMetadata('userId'), 'userId'), $property->read);
        self::assertEquals(new AccessDraft(new SlotAccessMetadata('userId'), 'userId'), $property->write);
        self::assertNull($property->strict);
        self::assertFalse($property->isOptional);
    }

    public function testNamesOfDirectionsAreIndependent(): void
    {
        $getter = new AccessDraft(new MethodAccessMetadata('getName'), 'name', true);
        $setter = new AccessDraft(new MethodAccessMetadata('setName'), 'title', true);

        $member = new MemberDraft('name()', $getter, $setter);

        self::assertSame('name', $member->toOutputField()?->name);
        self::assertSame('title', $member->toInputField()?->name);
    }

    public function testTypeKeepsAccessOfDirection(): void
    {
        $type = self::createType('int');
        $hook = new AccessDraft(new HookAccessMetadata('id'), 'id');

        $property = MemberDraft::createForProperty('id')->withWriteAccess($hook)->withType($type);

        self::assertInstanceOf(SlotAccessMetadata::class, $property->read?->access);
        self::assertSame($hook->access, $property->write?->access);
        self::assertSame($type, $property->read->type);
        self::assertSame($type, $property->write->type);
    }

    public function testTypeDoesNotAddMissingDirection(): void
    {
        $type = self::createType('int');

        $property = MemberDraft::createForProperty('id')
            ->withReadAccess(null)
            ->withType($type)
            ->withReadType($type);

        self::assertNull($property->read);
        self::assertSame($type, $property->write?->type);
        self::assertNull($property->withWriteAccess(null)->withWriteType($type)->write);
    }

    public function testFieldsTakeValuesOfTheirDirections(): void
    {
        $read = self::createType('int');
        $write = self::createType('string');
        $default = new DefaultValueMetadata(1, new SourceInfo());
        $skip = [new NullSkipCondition()];

        $property = MemberDraft::createForProperty('id')
            ->withName('user_id')
            ->withWriteAccess(new AccessDraft(new HookAccessMetadata('id'), 'user_id', true))
            ->withReadType($read)
            ->withWriteType($write)
            ->withSkipConditions($skip)
            ->withDefaultValue($default)
            ->withAliases(['uid'])
            ->withAliasConflict(AliasConflict::UseFirst)
            ->withStrict(true)
            ->withTypeErrorMessage('type')
            ->withUndefinedErrorMessage('undefined');

        $input = $property->toInputField();
        $output = $property->toOutputField();

        self::assertNotNull($input);
        self::assertSame('user_id', $input->name);
        self::assertEquals(new HookAccessMetadata('id'), $input->access);
        self::assertSame($write, $input->type);
        self::assertSame(['uid'], $input->aliases?->names);
        self::assertSame(AliasConflict::UseFirst, $input->aliases->conflict);
        self::assertTrue($input->strict);
        self::assertSame($default, $input->default);
        self::assertTrue($input->isOptional);
        self::assertSame('type', $input->typeErrorMessage);
        self::assertSame('undefined', $input->undefinedErrorMessage);

        self::assertNotNull($output);
        self::assertSame('user_id', $output->name);
        self::assertEquals(new SlotAccessMetadata('id'), $output->access);
        self::assertSame($read, $output->type);
        self::assertSame($skip, $output->skip);
    }

    public function testOptionalFieldWithoutDefaultValueWritesNothing(): void
    {
        $input = MemberDraft::createForProperty('id')->withOptional(true)->toInputField();

        self::assertTrue($input?->isOptional);
        self::assertNull($input->default);
    }

    public function testFieldWithoutAliasesHasNoAliases(): void
    {
        self::assertNull(MemberDraft::createForProperty('id')->toInputField()?->aliases);
    }

    public function testMissingDirectionHasNoField(): void
    {
        $property = MemberDraft::createForProperty('id')->withReadAccess(null)->withWriteAccess(null);

        self::assertNull($property->toInputField());
        self::assertNull($property->toOutputField());
    }

    public function testWitherDoesNotChangeOriginal(): void
    {
        $property = MemberDraft::createForProperty('id');

        $property->withName('user_id');
        $property->withDefaultValue(new DefaultValueMetadata(1, new SourceInfo()));

        self::assertSame('id', $property->read?->name);
        self::assertFalse($property->read->isNameExplicit);
        self::assertNull($property->default);
    }

    /**
     * @param non-empty-string $name
     */
    private static function createType(string $name): TypeMetadata
    {
        return TypeMetadata::createFromType(new NamedTypeNode(new Name($name)), new SourceInfo());
    }
}
