<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Mapping\Reference\Reader;

use PHPUnit\Framework\Attributes\Before;
use TypeLang\Mapper\Mapping\Reference\Reader\NativeReferencesReader;
use TypeLang\Mapper\Mapping\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\Tests\Mapping\Reference\ReferenceTestCase;
use TypeLang\Mapper\Tests\Mapping\Reference\Stub\ClassWithGroupUsesStub;
use TypeLang\Mapper\Tests\Mapping\Reference\Stub\MultipleNamespacesClassStub;
use TypeLang\Mapper\Tests\Mapping\Reference\Stub\SimpleClassStub;

final class NativeReaderTest extends ReferenceTestCase
{
    private readonly ReferencesReaderInterface $reader;

    #[Before]
    protected function setUpReader(): void
    {
        $this->reader = new NativeReferencesReader();
    }

    public function testBuiltinClassUses(): void
    {
        $statements = $this->reader->getUseStatements(
            class: new \ReflectionClass(\ReflectionClass::class),
        );

        self::assertSame([], $statements);
    }

    public function testSimpleUses(): void
    {
        $statements = $this->reader->getUseStatements(
            class: new \ReflectionClass(SimpleClassStub::class),
        );

        self::assertSame([
            0 => 'Some\Any',
            'Example' => 'Some\Any\Test',
        ], $statements);
    }

    public function testMultipleClasses(): void
    {
        $statements = $this->reader->getUseStatements(
            class: new \ReflectionClass(MultipleNamespacesClassStub::class),
        );

        $this->markTestIncomplete('TODO: Multiple classes is not supported');

        self::assertSame([
            0 => 'Some\Any2',
            'Example2' => 'Some\Any\Test2',
        ], $statements);
    }

    public function testGroupUses(): void
    {
        $statements = $this->reader->getUseStatements(
            class: new \ReflectionClass(ClassWithGroupUsesStub::class),
        );

        $this->markTestIncomplete('TODO: Group uses is not supported');

        self::assertSame([
            0 => 'Example\Some\Any1',
            'Example1' => 'Example\Some\Any\Test1',
            2 => 'Some\Any2',
            'Example2' => 'Some\Any\Test2',
        ], $statements);
    }
}
