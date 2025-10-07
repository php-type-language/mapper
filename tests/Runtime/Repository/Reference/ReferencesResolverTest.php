<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Runtime\Repository\Reference;

use TypeLang\Mapper\Runtime\Repository\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Mapper\Runtime\Repository\Reference\ReferencesResolver;
use TypeLang\Mapper\Tests\Runtime\Repository\Reference\Stub\SimpleClassStub;
use TypeLang\Parser\Node\FullQualifiedName;
use TypeLang\Parser\Node\Stmt\NamedTypeNode;

final class ReferencesResolverTest extends ReferenceTestCase
{
    public function testResolveWithSimpleNameInSameNamespace(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('TestClass');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TestClass', $result->name->toString());
    }

    public function testResolveWithUseStatement(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([
            'Any' => 'Some\\Any',
            'Example' => 'Some\\Any\\Test',
        ]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('Any');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('Some\\Any', $result->name->toString());
    }

    public function testResolveWithAliasedUseStatement(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([
            'Example' => 'Some\\Any\\Test',
        ]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('Example');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('Some\\Any\\Test', $result->name->toString());
    }

    public function testResolveWithNamespaceKeyword(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('namespace\\TestClass');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TypeLang\\Mapper\\Tests\\Runtime\\Repository\\Reference\\Stub\\TestClass', $result->name->toString());
    }

    public function testResolveWithNamespaceKeywordInGlobalNamespace(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('namespace\\TestClass');
        $context = new \ReflectionClass(\stdClass::class); // stdClass is in global namespace

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TestClass', $result->name->toString());
    }

    public function testResolveWithFullyQualifiedName(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode(new FullQualifiedName('\\Some\\Fully\\Qualified\\Class'));
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('\\Some\\Fully\\Qualified\\Class', $result->name->toString());
    }

    public function testResolveWithNonExistentClassInNamespace(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('NonExistentClass');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        // Should return original statement if class doesn't exist in namespace
        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('NonExistentClass', $result->name->toString());
    }

    public function testResolveWithComplexNamespaceKeyword(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('namespace\\Sub\\Namespace\\TestClass');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('TypeLang\\Mapper\\Tests\\Runtime\\Repository\\Reference\\Stub\\Sub\\Namespace\\TestClass', $result->name->toString());
    }

    public function testResolveWithMixedUseStatements(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([
            'Any' => 'Some\\Any',
            'Example' => 'Some\\Any\\Test',
            'GlobalClass', // No alias, just the class name
        ]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('Example');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertInstanceOf(NamedTypeNode::class, $result);
        self::assertSame('Some\\Any\\Test', $result->name->toString());
    }

    public function testResolvePreservesOriginalStatementWhenNoResolutionNeeded(): void
    {
        $reader = $this->createMock(ReferencesReaderInterface::class);
        $reader->method('getUseStatements')->willReturn([]);

        $resolver = new ReferencesResolver($reader);
        $statement = new NamedTypeNode('\\Fully\\Qualified\\Class');
        $context = new \ReflectionClass(SimpleClassStub::class);

        $result = $resolver->resolve($statement, $context);

        self::assertSame($statement, $result);
    }
}
