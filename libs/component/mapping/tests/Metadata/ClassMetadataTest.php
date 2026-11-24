<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Tests\Metadata;

use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\AliasConflict;
use TypeLang\Mapper\Mapping\Metadata\AliasesMetadata;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Mapping\Metadata\DefaultValueMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\InputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\Field\OutputFieldMetadata;
use TypeLang\Mapper\Mapping\Metadata\SkipCondition\NullSkipCondition;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Mapper\Mapping\Metadata\Struct\InputStructMetadata;
use TypeLang\Mapper\Mapping\Metadata\Struct\OutputStructMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Tests\TestCase;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;

final class ClassMetadataTest extends TestCase
{
    public function testSerializationKeepsFieldsTypesAndSources(): void
    {
        $source = new SourceInfo('/app/mapping.yaml', '$.properties.id.type');
        $type = TypeMetadata::createFromType(new NamedTypeNode(new Name('int')), $source);
        $error = TypeMetadata::createFromError('Unexpected end of input', $source);

        $metadata = new ClassMetadata(
            name: \stdClass::class,
            input: new InputStructMetadata([
                new InputFieldMetadata(
                    name: 'id',
                    access: new HookAccessMetadata('id'),
                    type: $type,
                    aliases: new AliasesMetadata(['uid'], AliasConflict::UseLast),
                    strict: true,
                    default: new DefaultValueMetadata(1, $source),
                ),
            ], 'Message'),
            output: new OutputStructMetadata([
                new OutputFieldMetadata('id', new MethodAccessMetadata('getId'), $error, [new NullSkipCondition()]),
            ], true),
        );

        self::assertEquals($metadata, \unserialize(\serialize($metadata)));
    }

    public function testClassWithoutFieldsHasEmptyStructures(): void
    {
        $metadata = new ClassMetadata(\stdClass::class);

        self::assertSame([], $metadata->input->fields);
        self::assertSame([], $metadata->output->fields);
        self::assertNull($metadata->input->typeErrorMessage);
        self::assertNull($metadata->output->normalizeAsArray);
        self::assertNull($metadata->discriminator);
    }
}
